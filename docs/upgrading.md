# Upgrading

## 0.3

`Instagres::create()` no longer accepts a client `$dbId` argument.

Call `create()` without that parameter. Read `id` from the returned array. Pass that `id` into `claimUrl($id)` when you need the claim URL for an existing database id.

The Claimable Postgres API assigns database ids. Claim URLs use the form `https://neon.new/claim/{id}`.
