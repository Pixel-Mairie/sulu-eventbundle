# Event Bundle

![GitHub release (with filter)](https://img.shields.io/github/v/release/Pixel-Mairie/sulu-eventbundle?style=for-the-badge)
[![Dependency](https://img.shields.io/badge/sulu-2.6-cca000.svg?style=for-the-badge)](https://sulu.io/)

## Presentation

This bundle allows to manage events for the Sulu CMS

## Features
* Events management
* List of events (via smart content)
* Preview
* Translation
* Settings
* SEO
* Activity log
* Trash

## Requirement
* PHP >= 8.1
* Symfony >= 5.4
* Composer

## Installation
### Install the bundle

Execute the following [composer](https://getcomposer.org/) command to add the bundle to the dependencies of your project:

```bash
composer require pixelmairie/sulu-eventbundle
```

### Enable the bundle

Enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

 ```php
 return [
     /* ... */
     Pixel\EventBundle\EventBundle::class => ['all' => true],
 ];
 ```

### Update schema
```shell script
bin/console do:sch:up --force
```

## Bundle Config

Define the Admin Api Route in `routes_admin.yaml`
```yaml
event.events_api:
  type: rest
  prefix: /admin/api
  resource: pixel_event.events_route_controller
  name_prefix: event.

event.settings_api:
  type: rest
  prefix: /admin/api
  resource: pixel_event.settings_route_controller
  name_prefix: event.
``` 

## Use
### Add/Edit an event
Go to the "Events" section in the administration interface. Then, click on "Add".
Fill the fields that are needed for your use.

Here is the list of the fields:
* Name (mandatory)
* Start date (mandatory)
* End date
* URL (mandatory and filled automatically according to the title)
* Website
* Email
* Phone number
* Logo
* Cover
* Images gallery
* PDF
* Description (mandatory)
* Location

Once you finished, click on "Save"

Your event is not visible on the website yet. In order to do that, click on "Activate?". It should be now visible for visitors.

To edit an event, simply click on the pencil at the left of the event you wish to edit.

### Remove/Restore an event

There are two ways to remove an event:
* Check every event you want to remove and then click on "Delete"
* Go to the detail of an event (see the "Add/Edit a news" section) and click on "Delete".

In both cases, the event will be put in the trash.

To access the trash, go to the "Settings" and click on "Trash".
To restore an event, click on the clock at the left. Confirm the restore. You will be redirected to the detail of the event you restored.

To remove permanently an event, check all the events you want to remove and click on "Delete".

## Settings

This bundle comes with settings. To access the bundle settings, go to "Settings > Events management".

Here is the list of the different settings:
* Default image
* Events limit on homepage

The default image is helpful when an event has no cover for example.

The events limit on home page allows you to choose the number of events you want to display on the homepage

## Twig extension
This bundle comes with only one twig function:

**event_settings()**: returns the settings of the bundle. No parameters are required.

Example of use:
```twig
{% set settings = event_settings() %}
{% if settings.defaultImage is defined %}
    {% set defaultImage = sulu_resolve_media(settings.defaultImage.id, 'fr') %}
{% endif %}
```

## Display event on homepage
As you saw in the "Settings" section, a bunch of events are displayed on the homepage.
By default, the number of events is 6. However, you can change this limit by editing the "Events limit on homepage" settings.

To retrieve this limit, you can add the following code:
```twig
{% block javascripts %}
    {{ parent() }}
    {% set settings = event_settings() %}
    {% set limitSetting = settings.limitBlockEvent ? settings.limitBlockEvent : 6 %}
    <script>
        const EVENTS_URL = "{{ path('events_block', {limit: limitSetting}) }}";
    </script>

{% endblock %}
```

## Contributing
You can contribute to this bundle. The only thing you must do is respect the coding standard we implements.
You can find them in the `ecs.php` file.
