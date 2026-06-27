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

import type { publish as def } from './types'
import semver from 'semver'
import {
  args,
  getActiveVersion,
  getPackageInfo,
  publishPackage,
  step,
} from './utils'

export const publish: typeof def = async ({
  defaultPackage,
  getPkgDir,
  provenance,
}) => {
  const tag = args._[0]
  if (!tag)
    throw new Error('No tag specified')

  let pkgName: string | undefined = defaultPackage
  let version: string | undefined

  if (tag.includes('@'))
    [pkgName, version] = tag.split('@')
  else version = tag

  if (!version)
    throw new Error('No version specified')

  if (!pkgName)
    throw new Error('No package name specified')

  if (typeof version === 'string' && version.startsWith('v'))
    version = version.slice(1)

  const { pkg, pkgDir } = getPackageInfo(pkgName, getPkgDir)
  if (pkg.version !== version) {
    throw new Error(
      `Package version from tag "${version}" mismatches with current version "${pkg.version}"`,
    )
  }

  const activeVersion = await getActiveVersion(pkg.name)

  step('Publishing package...')
  const releaseTag = version.includes('beta')
    ? 'beta'
    : version.includes('alpha')
      ? 'alpha'
      : activeVersion && semver.lt(pkg.version, activeVersion)
        ? 'previous'
        : undefined
  await publishPackage(pkgDir, releaseTag, provenance)
}

publish({ defaultPackage: 'dpi', provenance: false })
