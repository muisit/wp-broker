# wp-broker
Wordpress Broker: a cross-site linking plugin that mimics social media integration, but keeps everything on distributed servers

## Introduction
This plugin creates a social media experience for users of your local Wordpress installation. It makes your Wordpress site a
mini-Facebook-like site, where your users can collect and share stories using the regular Wordpress interfaces:
- view your 'wall' consisting of a chronologically ordered list of your posts, the posts you liked and the posts shared with you
- view a list of all your 'friends' and visit their walls with a single click
- automatically see new posts of friends when they are published
- share online content with your friends by creating a new post or directly sharing a link
- no advertisements, no popups, no 'suggested content' and no privacy violations
- stay in control over what you get to see and what you can post


## Activation
When this plugin is activated, it creates a new capability called `wpbroker`. All users with the `wpbroker` capability get a `Wall` 
menu option in the dashboard. You should add this capability to the roles you would like to enable using a capability management plugin.

Also, a scheduled task is created to run through the list of remote sites and retrieve new updates automatically whenever they become available.
The list of sites for which this task is run can be managed for each separate `Wall`.

## Registration
To allow cross site navigation and sharing, a central broker needs to be available. Users can register with that broker to get a broker
specific identifier. You can specify more than one broker and users can register with all brokers or only a subselection. Not all brokers
are supported by all sites, so make sure to support enough brokers to allow all the applicable sites to cross-register back. 

A default list of brokers is supplied with this plugin, but you can easily add a new broker manually. Brokers need to support the WPBroker API
interface. A default implementation for that is available on Github. Anyone can run a broker, though it is senseless to run your own if
no-one else knows of it.

## ThumbsUp API
To indicate your liking or disapproval of a remote post, you can interact with the respective UI elements on that site. The remote site
will then call a list of brokers with the request to process your activity. The remote site posts the activity (approve, etc.) and the 
post permalink to the intermediate broker. That broker will then process your registration cookie if it can find one. The first broker
that can access a registration cookie will use the information in that cookie to redirect the remote site activity post to a relevant
entry point in your own site.

As the broker registration cookie stays between you and the broker, the remote site never learns your site location directly. You can
opt in to return your site location to the remote site (never, ask, always) to allow the remote site to push new messages to you. 
As long as you have this remote site on a local watch-list, the new remotely pushed messages will be added to your Wall automatically.

In this way you can choose to follow a remote Wall and get all remote Wall messages or only original remote Wall messages.

A small diagram:
<!-- language: lang-none -->
    You                    Broker               Friend
                          register  <----------*
     start    *------->   register
     cookie   <-------*
              *--------------------------------> visit
              *--------------------------------> thumbsUp
                           action   <----------*
     action   <-------*
              *--------------------------------> optional feedback

As there can be more than 1 broker on either side, the exact route can be different for each action. No single broker can then see all
your activity. Also, once a link is established, new content can be pushed or retrieved between You and Friend directly, without going
through any broker.

## Activities
There are several activities you can perform:
- indicate you find something of a post
- follow a remote site passively (automatically retrieve new messages)
- follow a remote site actively (have the remote site push new messages)
- publish new posts yourself (default Wordpress functionality)
- share your posts with specific friends and connections (active push)

Giving a thumbs up to a remote post is the default behaviour, but a remote site can determine by itself what kind of actions it allows.
Whenever you perform an action there, the remote site can decide itself what to do with it. There is no magical way to check that the
remote site actually has 500K likes, you'll have to trust it on face value (or just not trust it, it's a senseless metric). 

The most important reasons for the remote activity are:
- allowing you to 'import' the remote post on your wall for your friends to see or to share to
- giving the remote site feedback on the post content

## Duplication filtering
When posts are pushed and shared automatically, it can be easy to cause a circular sharing situation. To prevent that, each post is 
marked with a unique identifier. Every post is only accepted once for each user or group. 

## Updates
When posts are updated, they get a new timestamp. You can have your site automatically request new versions of previously retrieved posts.
Also, sites can actively push new versions of a post to people subscribed to that post or site. 

