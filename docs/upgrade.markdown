Upgrade to a new version
========================

### From the archive (stable version)

1. Close your session (logout)
2. Rename your actual miniflux directory (to keep a backup)
3. Uncompress the new archive and copy your database file `db.sqlite` in the directory `data`
4. Make the directory `data` writeable by the web server user
5. Login and check if everything is ok
6. Remove the old miniflux directory

### From the repository (development version)

1. Close your session (logout)
2. `git pull`
3. Login and check if everything is ok

Note: Always do a backup of your database before!
