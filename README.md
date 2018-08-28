# testing
🔧 app for testing ownCloud

This app provides helpers to facilitate testing. This app is not intended to be used in production instances

## How to install

### Git

You can install the app via git in your app-folder

```
cd apps
git clone  https://github.com/owncloud/testing.git
php occ app:enable testing
```


## Publish latest version as github release

The latest published version can be found at https://github.com/owncloud/testing/releases/tag/latest

In order to update the latest version to be available, we need to move the github tag to a new HEAD.
Once the tag `latest` is pushed to github, drone-ci will build and publish the app to github