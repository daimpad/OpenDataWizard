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

import type { release as def } from './types'
import fs from 'node:fs'
import process from 'node:process'
import colors from 'picocolors'
import prompts from 'prompts'
import { publint } from 'publint'
import { printMessage } from 'publint/utils'

import semver from 'semver'

import { logRecentCommits } from './releaseUtils'
import {
  args,
  getPackageInfo,
  getVersionChoices,
  isDryRun,
  packagesFolder,
  run,
  runIfNotDry,
  step,
  updateVersion,
} from './utils'

export const release: typeof def = async ({
  repo,
  packages,
  logChangelog,
  generateChangelog,
  toTag,
  getPkgDir,
}) => {
  let targetVersion: string | undefined

  const selectedPkg: string
    = packages.length === 1
      ? packages[0]
      : (
          await prompts({
            type: 'select',
            name: 'pkg',
            message: 'Select package to release',
            choices: packages.map(i => ({ value: i, title: i })),
          })
        ).pkg

  if (!selectedPkg)
    return

  await logChangelog(selectedPkg)

  const { pkg, pkgPath, pkgDir } = getPackageInfo(selectedPkg, getPkgDir)

  const messages = await publint({ pkgDir })

  if (messages.length) {
    for (const message of messages) console.log(printMessage(message, pkg))
    const { yes }: { yes: boolean } = await prompts({
      type: 'confirm',
      name: 'yes',
      message: `${messages.length} messages from publint. Continue anyway?`,
    })
    if (!yes)
      process.exit(1)
  }

  if (!targetVersion) {
    const { release }: { release: string } = await prompts({
      type: 'select',
      name: 'release',
      message: `Select release type ${colors.dim(`(current: ${pkg.version})`)}`,
      choices: getVersionChoices(pkg.version),
    })

    if (release === 'custom') {
      const res: { version: string } = await prompts({
        type: 'text',
        name: 'version',
        message: 'Input custom version',
        initial: pkg.version,
      })
      targetVersion = res.version
    }
    else {
      targetVersion = release
    }
  }

  if (!semver.valid(targetVersion))
    throw new Error(`invalid target version: ${targetVersion}`)

  const tag = toTag(selectedPkg, targetVersion)

  if (targetVersion.includes('beta') && !args.tag)
    args.tag = 'beta'

  if (targetVersion.includes('alpha') && !args.tag)
    args.tag = 'alpha'

  const { yes }: { yes: boolean } = await prompts({
    type: 'confirm',
    name: 'yes',
    message: `Releasing ${pkg.name} ${colors.yellow(tag)} Confirm?`,
  })

  if (!yes)
    return

  // Ask confirmation for major version
  if (semver.major(targetVersion) > semver.major(pkg.version)) {
    const { yes }: { yes: boolean } = await prompts({
      type: 'confirm',
      name: 'yes',
      message: `Releasing major version ${colors.yellow(tag)} Confirm?`,
    })

    if (!yes)
      return
  }

  const { stdout: hasUncommitted } = await run('git', ['diff'], { stdio: 'pipe' })
  if (hasUncommitted) {
    const { yes }: { yes: boolean } = await prompts({
      type: 'confirm',
      name: 'yes',
      message: `${colors.yellow('There are uncommitted changes in the current repository. Proceed?')}`,
    })

    if (!yes)
      return
  }

  step('\nUpdating package version...')
  updateVersion(pkgPath, targetVersion)
  await generateChangelog(selectedPkg, targetVersion)

  const { stdout } = await run('git', ['diff'], { stdio: 'pipe' })
  if (stdout) {
    step('\nCommitting changes...')
    await runIfNotDry('git', ['add', '-A'])
    await runIfNotDry('git', ['commit', '-m', `release: ${tag}`])
    await runIfNotDry('git', ['tag', tag])
  }
  else {
    console.log('No changes to commit.')
    return
  }

  step('\nPushing to GitLab...')
  await runIfNotDry('git', ['push', 'origin', `refs/tags/${tag}`])
  await runIfNotDry('git', ['push'])

  if (isDryRun) {
    console.log('\nDry run finished - run git diff to see package changes.')
  }
  else {
    console.log(
      colors.green(
        `
Pushed, publishing should start shortly on CI.
https://gitlab.com/piveau/ui/${repo}/-/pipelines`,
      ),
    )
  }

  console.log()
}

function callRelease(packages: any) {
  return release({
    repo: 'dpiv3',
    packages,
    toTag: (pkg, version) =>
      pkg === 'dpi' ? `v${version}` : `${pkg}@${version}`,
    logChangelog: pkg => logRecentCommits(pkg),
    generateChangelog: async (pkgName) => {
    // if (pkgName === 'create-vite')
    //   await updateTemplateVersions()

      console.log(colors.cyan('\nGenerating changelog...'))
      const changelogArgs = [
        'conventional-changelog',
        '-p',
        'angular',
        '-i',
        'CHANGELOG.md',
        '-s',
        '-r 0',
        '--commit-path',
        '.',
      ]
      if (pkgName !== 'dpi')
        changelogArgs.push('--lerna-package', pkgName)
      await run('pnpm', ['--package', 'conventional-changelog-cli@3.0.0', 'dlx', ...changelogArgs], { cwd: `packages/${pkgName}` })
    },
  })
}

fs.readdir('packages', (error, files) => {
  if (error) {
    console.error(error)
  }
  else {
    const packages = files.filter((file) => { // Filter out directories
      return fs.statSync(`${packagesFolder}/${file}`).isDirectory()
    })
    callRelease(packages)
  }
})
