# LockTools

Debug and test tools for ownCloud transactional locking

## Lock Viewer

Get a list of all lock operations with live updating

![lockview](https://i.imgur.com/sdygqx1.png)

## Lock API

OCS Api to check, add and remove locks

The body of all `PUT` methods should consist of 'path' (`/$user/files/....`) and 'type' (1 for shared, 2 for exclusive) as x-www-form-urlencoded

 - List logged locks: `GET ocs/v1.php/apps/locktools/log`
 - Acquire a lock: `PUT ocs/v1.php/apps/locktools/log`
 - Release a lock: `PUT ocs/v1.php/apps/locktools/unlog`
 - Change a lock: `PUT ocs/v1.php/apps/locktools/changelock` (type is the type to set the lock *to*)
