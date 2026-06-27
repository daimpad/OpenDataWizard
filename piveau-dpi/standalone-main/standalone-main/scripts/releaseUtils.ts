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
import { readdirSync, writeFileSync } from 'node:fs'
import path from 'node:path'
import { execa } from 'execa'
import fs from 'fs-extra'
import colors from 'picocolors'

export async function run(
  bin: string,
  args: string[],
  opts: ExecaOptions<string> = {},
): Promise<ExecaReturnValue<string>> {
  return execa(bin, args, { stdio: 'inherit', ...opts })
}

export async function getLatestTag(pkgName: string): Promise<string> {
  const tags = (await run('git', ['tag'], { stdio: 'pipe' })).stdout.split(/\n/).filter(Boolean)
  const prefix = pkgName === 'vite' ? 'v' : `${pkgName}@`
  return tags
    .filter(tag => tag.startsWith(prefix))
    .sort()
    .reverse()[0] || ''
}

export async function logRecentCommits(pkgName: string): Promise<void> {
  const tag = await getLatestTag(pkgName)
  if (!tag)
    return
  const sha = await run('git', ['rev-list', '-n', '1', tag], {
    stdio: 'pipe',
  }).then(res => res.stdout.trim())
  console.log(
    colors.bold(
      `\n${colors.blue('i')} Commits of ${colors.green(
        pkgName,
      )} since ${colors.green(tag)} ${colors.gray(`(${sha.slice(0, 5)})`)}`,
    ),
  )
  await run(
    'git',
    [
      '--no-pager',
      'log',
      `${sha}..HEAD`,
      '--oneline',
      '--',
      `packages/${pkgName}`,
    ],
    { stdio: 'inherit' },
  )
  console.log()
}

export async function updateTemplateVersions(): Promise<void> {
  const viteVersion = fs.readJSONSync('packages/vite/package.json').version
  if (/beta|alpha|rc/.test(viteVersion))
    return

  const dir = 'packages/create-vite'
  const templates = readdirSync(dir).filter(dir =>
    dir.startsWith('template-'),
  )
  for (const template of templates) {
    const pkgPath = path.join(dir, template, 'package.json')
    const pkg = fs.readJSONSync(pkgPath)
    pkg.devDependencies.vite = `^${viteVersion}`
    writeFileSync(pkgPath, `${JSON.stringify(pkg, null, 2)}\n`)
  }
}
