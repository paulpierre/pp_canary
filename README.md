# Canary
- - -

### Canary is a platform that helps anticipate fraud and fulfillment delays in Shopify


![Canary](http://paulpierre.com/img/canary.jpg)


built by [http://paulpierre.com](http://paulpierre.com) in 2017-2018

Canary is a tool I built for my e-commerce team to help address fulfillment problems. Shipments from China would be held up in customs, or customers would never receive an item and eventually after a volume of data we were able to develop heuristics around this and build a tool that was not available to the public at the time. 

![Canary](http://paulpierre.com/img/canary_1.jpg) 

![Built with](https://i.imgur.com/IRsBvDx.png) 


I built the platform end to end using the basic Linux LAMP stack in conjunction with a tracking API from Chinese tracking company 17track. Every few hours I would poll our Shopify API and 17 Track to check the fulfillment status and based on the codified heuristics we were able to determine the likelihood status of orders so our fulfillment team can react in time and salvage a sale/fulfillment process from an upset customer and ultimately save our company hundreds and thousands of dollars in the process. 

Live app and servers no longer maintained, but demo available upon request.

---

### Documentation

*****
Index
*****
1. Purpose of application
2. Customer Lifecycle
3. App Lifecycle
4. Directory Structure
5. Setup
6. Apache Setup


### Purpose of application
---

    This application is supposed to track customer orders. It grabs information from the Shopify API:

    https://help.shopify.com/api/reference

    and stores the relevant objects like Order and Fulfillment into our database. It also interfaces
    with Shopify's webhooks:

    https://help.shopify.com/api/reference/webhook

    To grab orders as they are created and determine the status of an order during it's entire lifetime.

    Shopify does NOT track the actual shipping of an order. It is only aware when an order is created,
    when it is fulfilled (meaning we take the order and we ship it to the customer). It is not aware
    of WHERE the package is. This application leverages the 17track.net API to determine the state of
    shipment.

    The key objective of this application is to keep awareness of the state of an order during the
    shipping process. So to summarize, this app stores Shopify order information into the database
    and checks shipping status for orders hourly.

    In the front end, we must display high risk orders so we can bring it to the attention of the team.




### Customer Lifecycle
---

    Let me explain how the high level lifeycle of our Shopify works.

    1) Customer makes an order. We store customer order information like:
        • Customer shipping details
        • Product details like SKU, price, etc.
    2) Order is stored in Shopify database, we also store this.
    3) When we are ready to fulfill the product (e.g. package and ship to customer) a fulfillment is created.
        Fulfillment information includes:
        • Tracking number and carrier
        • Status of shipment
    4) A customer may make MULTIPLE ORDERS, so each may be shipped at different times, and thus fulfillment
        events will be different for different items in an order
    5) An order is considered complete when all products ordered have been fulfilled.




### App Lifecycle
---
    Now that I've explained the customer lifecycle, it's important to understand how this translates into
    code. The core life cycle of the app is

    1) Grab ORDER data including customer information, product ordered. This will come from:
        • Hourly cron job from: api/scripts/canary_orders which grabs latest orders from Shopify from the last hour
        • Shopify webhooks. Shopify will PUSH via HTTP REST to our servers Order and Fulfillment Object data to our
          API endpoints. Currently there are 4:

            ------------------
            Fulfillment Create
            ------------------
                URL: http://api.thecanary.io/webhook/fulfillment/create/
                This is when we decide to fulfill part of an order. If our team ships an item for
                a customer, they will send JSON to the above URL real-time.

            ------------------
            Fulfillment Update
            ------------------
                URL: https://api.thecanary.io/webhook/fulfillment/update/
                If anything changes within a fulfillment, like a tracking number has been added
                Shopify will fire the current Fulfillment object to this end point

            ---------------
            Order fulfilled
            ---------------
                URL: https://api.thecanary.io/webhook/order/fulfilled/
                When an order has been fulfilled by our shipping partner

            ------------
            Order Update
            ------------
                URL: http://api.thecanary.io/webhook/order/update/
                If Order information has been changed, for example if a customer requests a refund, their
                Order object will be changed and we will get this information


            **NOTE**: We do not request ORDER CREATE webhook, because we are already grabbing it hourly from the
            API as stated in the first point above. We only need webhooks for when any data about the ORDER
            or FULFILLMENT is MODIFIED

    2) External data sources like CRONJOBS and WEBHOOKS send JSON data to our controller end points. These
        controllers parse the data and update the models which stores it into our database.

    3) Above is the flow for creating and updating our data structure from Shopify. But Shopify does not understand
        the state of an Order's SHIPPING STATUS. We get this information from a shipment tracking API called 17track.net
        Every hour the BASH SCRIPT api/scripts/canary_crawler.sh is ran via CRON JOB. The lifecycle for this is:

        • Run canary_crawler.sh every hour, this will update the shipment tracking status for all fulfillment records in the DB

        • canary_craw.sh:
            - Grab fulfillment records that have not been checked in 1 day
            - Grab order_id and fulfillment_id and tracking_number and convert list to JSON
            - Pass JSON to controller "crawler.controller.php"

        • crawler.controller.php
            - Connect to 17track.net API and grab status of the ORDER
            - Pass results to tracking.controller.php

        • tracking.controller.php
            - Determine status and whether we need to write alert status, this will be explained later
            - If call is successful to 17track.net, store this in api_status so we monitor API usage since we are charged per call
            - Store status into Fulfillment object and to the database


    3) That's it! Basically the app needs to store ALL information about orders. The status of the shipment of the orders.
        This is the back-end. Basically the front-end will display order status information that is
        important for the team to know so they can react quickly with shipments that have problems.

        Details on the dashboard are here:

        https://docs.google.com/document/d/1huP_YYSE1jTC16-N2f11vTdeSDikYQWgYtj4ysoArmk/edit



### Directory Structure
---

    The app is a customer framework I created. .htaccess grabs the URL and routes it MVC style.
    This is to help redundancy. Controllers match the name of models and are all loaded automatically.

    An API object like "order" (url: http://api.thecanary.io/order/) has a matching controller and
    model.


    ---------
    index.php
    ---------

    The application uses APACHE rewrites via .htaccess. Please enable this in your apache configuration file.

    Basically when the browser requests a URL, .htaccess will pass the query string to index.php instead
    of accessing a file or directory directly. It will skip index.php if the file DOES exist.

    So, if the browser requests http://api.thecanary.io/controllerObject/controllerFunction/controllerID/controllerData

    index.php will grab $_SERVER['REQUEST_URI'] (or argv for BASH SHELL SCRIPTS) and explode the URL
    into an array. Each "/" will denote different parts of the controller.

     • controllerObject
            This is the name of the controller, the first part of the URL. index.php will look for a file
            in the api/controller directory and execute it. It will also pass the arguments from the URL
            via: global $controllerObject,$controllerFunction,$controllerID,$controllerData

     • controllerFunction
            This is what your want to do with the object

     • controllerID
            This is the object's row ID in the database when you want to reference a particular object

     • controllerData
            These are additional parameters you want to pass to the object's controller

     At the end, when the API requests an object, it needs to provide a response to the client.
     This is done via the api_response() function in index.php

     The structure for the response is in JSON and is structured like this:

     [{
        code: <response_code>,
        msg: "<message_to_client>",
        data: {
            <supporting_data_for_query>
        }
     }]


    ----------
    api/backup
    ----------
    IGNORE THIS DIRECTORY. Contains backup files form previous versions of the app that are deprecated
    but kept in the repository for future purposes


    ---------
    api/class
    ---------

    Contains classes for objects that have complex functionality

        • crawler.class.php - IGNORE THIS FILE. IT IS NO LONGER USED


    --------------
    api/controller
    --------------

    Contains controllers for API object end-points. routed from index.php

        • crawler.controller.php
            The Crawler controller is called by the system cron job which crawls fulfillment records flagged in the
            database for shipment tracking. Everyday it will crawl http://17track.net to parse and store / update the
            shipping status of all outstanding fulfillment orders.

        • cron.controller.php
            This controller gets called by the system CRON JOB to grab hourly data from Shopify API
            and insert new Orders and related Fulfillment data

            It also contains spreadsheet parsing data for inserting new tracking #s
            This will be a separate project and explained later

        • manual.controller.php
            You can IGNORE THIS file, this is used for debugging and for me to manually insert
            data from the Shopify API when I have issues or am missing data. This file can be
            explained later, but will not be used by you

        • order.controller.php
            This file will process Order information whether it is Order creation which will create
            Item objects as well or Order UPDATE which will grab an order object, query if it exists
            in the database, if it does, only update NEW information in the relevant columns. It will
            also parse through Order data and create or update related Fulfillment objects

        • report.controller.php
             This is the main API endpoint for the dashboard. This will request a particular SQL
             report query from the file sql_queries.php and return the appropriate JSON to the
             user-facing client. This is where you will be adding a lot of code

        • spytool.controller.php
            Ignore this file, I will delete it later.

        • test.controller.php
            Ignore this file, I use this for debugging and testing when adding new controllers or features

        • tracking.controller.php
            This controller is used internally. crawler.controller.php will pass this file the
            JSON results from the tracking API 17track.net and will PARSE this data and update
            the relevant objects and the database.

            This will also keep track of the tracking API usage

        • webhook.controller.php
            This controller will process data sent from the Shopify API webhooks, please refer above
            where I explain all the webhooks



    --------
    api/data
    --------

    For now ignore this folder. This directory is used for another related feature in this project which
    you will work on after the dashboard. Basically when a user uploads EXCEL files, this is where they
    are stored and archived.


    --------
    api/logs
    --------

    This is where log files we generate for debugging are stored. Please add this to the .gitignore file
    on your local and stage machine.


    ---------
    api/model
    ---------

    This contains all the models for all the objects in the API. Basically models represent the data
    structure for our objects and are in charge for storing the data into the database.

        • crawler.model.php
            IGNORE THIS, currently not used

        • fulfillment.model.php
            Holds the data structure and database functions for Fulfillment objects
            This is the most important part of the application because the user of our
            dashboard needs to understand specific statuses of the order process that
            this model must indicate, specifically "delivery_status" and "alert_status"
            I will explain this more later.

            A Fulfillment object is the child of an Order object.

        • item.model.php
            This contains details about an Item in Shopify which is a product a customer
            ordered. An item object is the child of an Order object.

        • order.model.php
            This contains details of an order. This is the parent object. This model
            contains functions to add, create, pull a list of Orders from the database

    -----------
    api/scripts
    -----------

    This directory contains BASH SHELL SCRIPTS that are executed in specific intervals determined
    by CRONJOBs. Only two scripts are used:


        • canary_crawler.sh
            This is ran EVERY HOUR to pull eligible Fulfillment objects and check the 17track API
            for the current status of an order's shipment, and updates the database with this
            data.

        • canary_orders.sh
            This is ran EVERY HOUR to pull Order objects from the Shopify API and store new
            Orders and relevant Fulfillments and Items in the database.



    --------------
    api/shared/lib
    --------------

    Contains shared libraries used by the application

        • php-excel-reader/
            You will use this for the next project. IGNORE THIS FOR NOW

        • phpFastCache/
            Currently not used, but will be used later for caching MySQL queries for better app performance

        • database.class.php
            This should really be in the class folder, but since I re-use this alot, I put it in the
            shared folder. This class is basically the interface to the database. It contains
            functions that allow to create, delete, add, and update rows in the database via
            a simple Array hash methodology.

            EVERY MODEL INHERITS THIS CLASS to make it easy to store and modify Object data to the
            database. DO NOT modify this class as it has been optimized already. If you wish to modify
            it or optimize it, just let me know

        • utility.php
            Contains useful functions I use universally across all applications. Feel free to add to this.

        • other files
            Ignore these, they will be used for the next project for this application


    -------
    api/tmp
    -------

    Temporary directory, mostly used by phpFastCache library. Ignore for now.


    ----------
    config.php
    ----------

    This contains app configuration information.

    • ENABLE_CACHE
        Make sure this stays false for now

    • MODE
        Within this switch statement, I've added your local machine information so that the codebase
        works on both our machines as well as our stage and production machines. Please modify and edit
        the CASE statements that are relevant only to YOUR server

    • ENABLE_DEBUG
        This will set the app in debug mode and display data in certain areas VERBOSELY. You will not
        really need this, I use this flag for testing to make sure data is accurate, etc.

    • ENABLE_LOGS
        When set to TRUE this will write to the HTTP error log in Apache and display useful data as
        the app runs through its lifecycle. This is extremely helpful to me during debugging an app
        when adding a new feature.

        If you want to write to the error log, PLEASE use the function: log_error($output)
        It will write to the error log if ENABLE_LOGS == TRUE. If FALSE, it will ignore log_error
        and not write to the HTTP error log

    -------------
    constants.php
    -------------

    Contains constants. You and I will add to this as the project grows. Contains API data as well.


    ---------------
    sql_queries.php
    ---------------

    Since the dashboard reporting will contain complex JOINs in MySQL, I have put all the reporting
    queries inside this file. Basically the reporting queries are set as CONSTANTS in a pre-defined
    format so it is easy to edit. When report.controller.php grabs the query string, it will replace
    the relevant variables in the query string like {ID} or {DATE_RANGE} to execute the query for
    the dashboard.

    You will be heavily editing this file.



### Model & Controller structure
---

    The way I designed the Model files is so that you only need to declare the class/instance variables
    once inside the class file.

    It will dynamically also generate the setter and getter functions, although I don't really use them.

    Each model has a serialize_object function which will grab all the instance variables
    and convert them to an Array which will be used by database.class.php to store into the DB

    You can initialize any model and if you do not pass a parameter in the contructor, it will create
    a blank Object.

    If you pass the constructor an Array, it will create an Object with these values in the respective
    instance variables

    If you pass the contructor an ID it will look up this row ID in the database and load the Object
    into the instance you've declared.

    So it's quite dynamic.



### API end points
---

    Internally there are a few endpoints, for the client-facing side like the dashboard
    it will only use the /report  end point

    • canary_crawl.sh - to update tracking status information in Fulfillment Objects
    • canary_orders.sh - to grab new Orders from Shopify API and store to DB


### Cron Jobs
---

    Currently there are only 2 cron jobs that run every hour.



### Dashboard
---

    The majority of your focus will be to build the front-end dashboard for the team.

    Details are here:

            https://docs.google.com/document/d/1huP_YYSE1jTC16-N2f11vTdeSDikYQWgYtj4ysoArmk/edit

    The template files are inside api/dashboard/

    report.js contains the logic for calling the API and arranging the data appropriately in the dashboard UI






### MIT License
- - -

Copyright (c) 2019 Paul Pierre

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in allcopies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.



