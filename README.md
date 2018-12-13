# testing
🔧 app for testing ownCloud

[![Build Status](https://drone.owncloud.com/api/badges/owncloud/testing/status.svg?branch=master)](https://drone.owncloud.com/owncloud/testing)

This app provides helpers to facilitate testing. This app is not intended to be used in production instances

## How to install

### Git

You can install the app via git in your app-folder

```
cd apps
git clone https://github.com/owncloud/testing.git
php occ app:enable testing
```


## Publish latest version as github release

The latest published version can be found at https://github.com/owncloud/testing/releases/tag/latest

In order to update the latest version to be available, we need to move the git tag to a new HEAD.
In a local clone of this repository, move the tag by:

```
git fetch origin
git checkout master
git pull
git tag --force latest HEAD
git push --force --tags
```


Once the tag `latest` is pushed to github, drone-ci will build and publish the app to github.
