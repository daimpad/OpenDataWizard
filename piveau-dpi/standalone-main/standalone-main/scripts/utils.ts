/**
 * Modified from original code by Vite contributors, licensed under the MIT License.
 * See below for original MIT License terms.
 *
 * This modified code is licensed under the Apache License, Version 2.0, see LICENSE file in the project root.
 */

/**
 * @license
 * The MIT License (MIT)
 *
 * Copyright (c) 2023-present, Vite contributors
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

import type { Options as ExecaOptions, ExecaReturnValue } from 'execa'
import type { ReleaseType } from 'semver'
import { readFileSync, writeFileSync } from 'node:fs'
import path from 'node:path'
import process from 'node:process'
import { execa } from 'execa'
import minimist from 'minimist'
import colors from 'picocolors'
import semver from 'semver'

export const packagesFolder = 'packages'

export const args = minimist(process.argv.slice(2))

export const isDryRun = !!args.dry

if (isDryRun) {
  console.log(colors.inverse(colors.yellow(' DRY RUN ')))
  console.log()
}

export function getPackageInfo(
  pkgName: string,
  getPkgDir: ((pkg: string) => string) = pkg => `${packagesFolder}/${pkg}`,
) {
  const pkgDir = getPkgDir(pkgName)
  const pkgPath = path.resolve(pkgDir, 'package.json')
  const pkg = JSON.parse(readFileSync(pkgPath, 'utf-8')) as {
    name: string
    version: string
    private?: boolean
  }

  if (pkg.private)
    throw new Error(`Package ${pkgName} is private`)

  return { pkg, pkgDir, pkgPath }
}

export async function run(
  bin: string,
  args: string[],
  opts: ExecaOptions<string> = {},
): Promise<ExecaReturnValue> {
  return execa(bin, args, { stdio: 'inherit', ...opts })
}

export async function dryRun(
  bin: string,
  args: string[],
  opts?: ExecaOptions<string>,
): Promise<void> {
  return console.log(
    colors.blue(`[dryrun] ${bin} ${args.join(' ')}`),
    opts || '',
  )
}

export const runIfNotDry = isDryRun ? dryRun : run

export function step(msg: string): void {
  return console.log(colors.cyan(msg))
}

interface VersionChoice {
  title: string
  value: string
}
export function getVersionChoices(currentVersion: string): VersionChoice[] {
  const currentBeta = currentVersion.includes('beta')
  const currentAlpha = currentVersion.includes('alpha')
  const isStable = !currentBeta && !currentAlpha

  function inc(i: ReleaseType, tag = currentAlpha ? 'alpha' : 'beta') {
    return semver.inc(currentVersion, i, tag)!
  }

  let versionChoices: VersionChoice[] = [
    {
      // title: 'next',
      title: 'patch',
      value: inc(isStable ? 'patch' : 'prerelease'),
    },
  ]

  if (isStable) {
    versionChoices.push(
      {
        title: 'beta-minor',
        value: inc('preminor'),
      },
      {
        title: 'beta-major',
        value: inc('premajor'),
      },
      {
        title: 'alpha-minor',
        value: inc('preminor', 'alpha'),
      },
      {
        title: 'alpha-major',
        value: inc('premajor', 'alpha'),
      },
      {
        title: 'minor',
        value: inc('minor'),
      },
      {
        title: 'major',
        value: inc('major'),
      },
    )
  }
  else if (currentAlpha) {
    versionChoices.push({
      title: 'beta',
      value: `${inc('patch')}-beta.0`,
    })
  }
  else {
    versionChoices.push({
      title: 'stable',
      value: inc('patch'),
    })
  }
  versionChoices.push({ value: 'custom', title: 'custom' })

  versionChoices = versionChoices.map((i) => {
    i.title = `${i.title} (${i.value})`
    return i
  })

  return versionChoices
}

export function updateVersion(pkgPath: string, version: string): void {
  const pkg = JSON.parse(readFileSync(pkgPath, 'utf-8'))
  pkg.version = version
  writeFileSync(pkgPath, `${JSON.stringify(pkg, null, 2)}\n`)
}

export async function publishPackage(
  pkdDir: string,
  tag?: string,
  provenance?: boolean,
): Promise<void> {
  const publicArgs = ['publish', '-r', '--access', 'public', '--no-git-checks']
  if (tag)
    publicArgs.push('--tag', tag)

  if (provenance)
    publicArgs.push('--provenance')

  // Use npm directly instead of pnpm to publish for OIDC trusted publisher support
  // (pnpm doesn't support it yet?)
  // https://github.com/pnpm/pnpm/issues/9812
  await runIfNotDry('npm', publicArgs, {
    cwd: pkdDir,
  })
}

export async function getActiveVersion(
  npmName: string,
): Promise<string | undefined> {
  try {
    return (await run('npm', ['info', npmName, 'version'], { stdio: 'pipe' }))
      .stdout
  }
  catch (e: any) {
    // Not published yet
    if (e.stderr.startsWith('npm ERR! code E404'))
      return
    throw e
  }
}
