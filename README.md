## Features:
- Search nearby location
- Delivery areas (zones) boundary
- Location opening hours
- Component for displaying Opening Hours
- Component for displaying Menu Items
- Component for displaying Menu Categories
- Component for displaying Menu Reviews

### Admin Panel
Manage locations and menu items from the admin panel.

### Components
| Name     | Page variable                | Description                                      |
| -------- | ---------------------------- | ------------------------------------------------ |
| Search  | `<?= component('localSearch') ?>` | Display the nearby search box on the page |
| LocalBox  | `<?= component('localBox') ?>` | Display information about and manages the user's location |
| Info  | `<?= component('localInfo') ?>` | Display the opening hours of the user's location |
| List  | `<?= component('localBox') ?>` | Display a list of locations on the page |
| Menu  | `<?= component('localMenu') ?>` | Display a list of menu items on the page |
| Categories | `<?= component('categories') ?>` | Displays menu categories on the page            |
| Review  | `<?= component('localReview') ?>` | Display a list of reviews on the page |
| Gallery | `<?= component('gallery') ?>` | Displays the location gallery on the page            |

### Search Component

**Properties**

| Property                 | Description              | Example Value | Default Value |
| ------------------------ | ------------------------ | ------------- | ------------- |
| hideSearch                     | Hides the search field            | true/false        | false         |
| menusPage                     | Page name to the menus page            | local/menus         | local/menus         |

**Variables available in templates**

| Variable                  | Description                                                  |
| ------------------------- | ------------------------------------------------------------ |
| $menusPage | Link to the menus page                                    |
| $hideSearch | Used to hide the search field and show a view menu button instead                             |
| $location | Location Class instance |

**Example:**

```
---
title: 'Home'
permalink: /

'[localSearch]':
    hideSearch: 0
    menusPage: local/menus
---
...
<?= component('localSearch') ?>
...
```

### LocalBox Component

**Properties**

| Property                 | Description              | Example Value | Default Value |
| ------------------------ | ------------------------ | ------------- | ------------- |
| paramFrom                     | URL routing code used for determining the location slug           | location        | location         |
| redirect                     | Page name to redirect to when location is not loaded            | home        | home         |
| showLocalThumb                     | Show/hide the current location's thumb            | true/false        | false         |
| menusPage                     | Page name to the menus page           |    local/menus     |     local/menus     |
| openTimeFormat                     | Time format to use to display the opening time |    H:i     |      system default    |
| timePickerDateFormat                     | Date format to use for the order timepicker |    D d    |    D d      |
| timePickerTimeFormat                     | Time format to use for the order timepicker    |    H:i     |     H:i     |
| timePickerDateTimeFormat                     | Date time format to use for the order timepicker   |    D d H:i     |     system default     |

**Variables available in templates**

| Variable                  | Description                                                  |
| ------------------------- | ------------------------------------------------------------ |
| $location | Instance of the location class                                                |
| $locationCurrent | Instance of the current location model                                                |
| $locationTimeslot | Delivery/Pick-up schedule order timeslot                                         |

**Example:**

```
---
title: 'Menus'
permalink: '/:location?local/menus/:category?'
...

'[localBox]':
    paramFrom: location
    showLocalThumb: 0
    menusPage: local/menus
    openTimeFormat: 'H:i'
    timePickerDateFormat: 'D d'
    timePickerTimeFormat: 'H:i'
    timePickerDateTimeFormat: 'D d H:i'
---
...
<?= component('localBox') ?>
...
```

### LocalInfo Component

**Variables available in templates**

| Variable                  | Description                                                  |
| ------------------------- | ------------------------------------------------------------ |
| $location | Instance of the location class                                                |
| $locationCurrent | Instance of the current location model                                                |
| $localPayments | Instances of available payment gateways                                          |
| $localHours | Collection of the location's working hour                                         |
| $deliveryAreas | Collection of the location's delivery areas                                         |

**Example:**

```
---
title: 'Menus'
permalink: '/:location?local/info'
...

'[localInfo]': { }
---
...
<?= component('localInfo') ?>
...
```

### LocalList Component

**Variables available in templates**

| Variable                  | Description                                                  |
| ------------------------- | ------------------------------------------------------------ |
| $distanceUnit | Unit of length to use for the distance. Uses system settings value                                               |
| $showReviews | Value determines whether to show reviews                                                |
| $timeFormat | Time format to use for location opening time                                                |
| $filterSearch | The user's search query                                                |
| $filterSorted | The user's selected filter                                                |
| $filterSorters | Array of available filters                                                |
| $userPosition | Instance of the user current position                                                |
| $locationsList | List of location matching the search and/or filters                                         |

**Example:**

```
---
title: 'Locations'
permalink: '/locations'
...

'[localList]': { }
---
...
<?= component('localList') ?>
...
```

### LocalMenu Component

**Properties**

| Property                 | Description              | Example Value | Default Value |
| ------------------------ | ------------------------ | ------------- | ------------- |
| $menusPerPage                     | Number of menu items per page           | 50        | 20         |
| $showMenuImages                     | Show/hide menu item images            | true/false        | false         |
| $menuImageWidth                     | Width of the menu item image            | 90        | 90         |
| $menuImageHeight                     | Height of the menu item image            | 85        | 85         |

**Variables available in templates**

| Variable                  | Description                                                  |
| ------------------------- | ------------------------------------------------------------ |
| $showMenuImages |                                                |
| $menuImageWidth |                                                 |
| $menuImageHeight |                                                 |
| $menuList | List of menu items                                              |

**Example:**

```
---
title: 'Menus'
permalink: '/:location?local/menus/:category?'
...

'[localMenu]':
    menusPerPage: 200
    showMenuImages: 0
    menuImageWidth: 95
    menuImageHeight: 80
---
...
<?= component('localMenu') ?>
...
```

### Categories Component

**Properties**

| Property                 | Description              | Example Value | Default Value |
| ------------------------ | ------------------------ | ------------- | ------------- |
| $menusPage | Page name of the menus page         |            |       |

**Variables available in templates**

| Variable                  | Description                                                  |
| ------------------------- | ------------------------------------------------------------ |
| $menusPage | Link to the location menus page                                               |
| $categories | Value holds the categories tree                                               |
| $selectedCategory | The user selected category                                               |

**Example:**

```
---
title: 'Menus'
permalink: '/:location?local/menus/:category?'
...

'[categories]':
    menusPage: local/menus
---
...
<?= component('categories') ?>
...
```

### LocalReviews Component

**Properties**

| Property                 | Description              | Example Value | Default Value |
| ------------------------ | ------------------------ | ------------- | ------------- |
| $pageLimit | Number of reviews per page       |   20    |     20      |
| $sort | Sort the review list             |    date_added asc    |     date_added asc      |
| $dateFormat | Date format to display the review date            |   d M y H:i  |     d M y H:i      |
| $redirectPage | Page name to redirect to when reviews is disabled       |   local/menus  |     local/menus      |

**Variables available in templates**

| Variable                  | Description                                                  |
| ------------------------- | ------------------------------------------------------------ |
| $reviewRatingHints | Array of rating hints. Ex. Good, Bad, ...          |
| $reviewList | List of reviews to display                                              |

**Example:**

```
---
title: 'Locations'
permalink: '/:location?local/reviews'
...

'[localReview]':
    pageLimit: 10
    sort: 'date_added asc'
---
...
<?= component('localReview') ?>
...
```

### LocalGallery Component

**Variables available in templates**

| Variable                  | Description                                                  |
| ------------------------- | ------------------------------------------------------------ |
| $gallery | List of location gallery images                                         |

**Example:**

```
---
title: 'Locations'
permalink: /:location?local/gallery
...

'[localGallery]':
---
...
<?= component('localGallery') ?>
...
```

### License
[The MIT License (MIT)](https://tastyigniter.com/licence/)