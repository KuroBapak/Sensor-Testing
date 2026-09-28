## Constraint: allow-remote=none by default
Project ini set `allow-remote=none` di config npm (kemungkinan lewat
.npmrc user-level atau sistem). Ini memblokir SEMUA npm install
package baru dengan error EALLOWREMOTE.

## Fix
Tambahkan `allow-remote=all` ke .npmrc project SEBELUM install package
baru apa pun:
    npm config set allow-remote all --location=project
atau langsung pakai flag: `npm install <pkg> --allow-remote=all`

## Kenapa begini
[isi kalau tahu alasannya — mis. kebijakan keamanan organisasi]