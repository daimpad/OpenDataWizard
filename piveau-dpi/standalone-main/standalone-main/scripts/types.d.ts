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

export declare function publish(options: {
  defaultPackage: string
  getPkgDir?: (pkg: string) => string
  /**
   * Enables npm package provenance https://docs.npmjs.com/generating-provenance-statements
   * @default false
   */
  provenance?: boolean
}): Promise<void>

export declare function release(options: {
  repo: string
  packages: string[]
  logChangelog: (pkg: string) => void | Promise<void>
  generateChangelog: (pkg: string, version: string) => void | Promise<void>
  toTag: (pkg: string, version: string) => string
  getPkgDir?: (pkg: string) => string
}): Promise<void>
