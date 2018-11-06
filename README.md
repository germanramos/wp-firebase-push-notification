# Wordpress Firebase Push Notification

## Description

 Notify your users of new posts with Firebase cloud messaging (FCM) for Android and iOS. Sends a FCM/push notification when a post is published for the first time or updated.

## Requires

Wordpress 3.6+


## Installation

1. Download the plugin .zip file

2. Login to admin Click Plugins -> Add New -> Upload

3. Find wp-firebase-push-notification Wordpress Plugin in plugin list and activate it.

4. Now goto Firebase Push Notification under Settings in admin menu

## Create zip of the plugin
```
cd ..
zip -r wp-firebase-push-notification.zip wp-firebase-push-notification -x *.git*
```

## How to use Plugin

1. After install goto Firebase Push Notification under Settings in Wordpress admin menu
2. Enter Google firebase api key to field given.
3. The firebase topic will be the slug of the first 3 categories of the post
4. If your post does not have category, no notification will be send
5. The test message will use topic "test"

## Authors

* [sony7596](https://profiles.wordpress.org/sony7596)
* [miraclewebssoft](https://profiles.wordpress.org/miraclewebssoft)
* [reachbaljit](https://profiles.wordpress.org/reachbaljit)
* [germanramos](https://github.com/germanramos/wp-firebase-push-notification)

## License

GPL v2
