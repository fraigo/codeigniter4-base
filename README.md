# CodeIgniter Quick Setup

* Download Codeigniter or create a Codeigniter project
* Start the web service locally
    * Run `php spark serve` to start a local dev at `http://localhost:8080`
    * Press `Ctrl+C` to stop the server
* Change home main view
    * Create a new view file `app/Views/home.php`
        * Include your own HTML code
    * Modify `app/Controllers/Home.php ` controller to use `home` view
    * Remove old view `app/Views/welcome_message.php`
* Setup `.env` file
    * Copy `env` file to `.env`
    * Uncomment `CI_ENVIRONMENT = development`
    * To setup sqlite database, uncomment and set:
        * `database.default.DBDriver = SQLite3`
        * `database.default.database = database.sqlite`
* Create a migration file (to create `user` table)
    * Use a meaningful name for the migration
        * `php spark make:migration CreateUser`
    * File `{TIMESTAMP}_CreateUser.php` is created in `app/Database/Migrations/`
        * Use `$this->forge->addField(["FIELDNAME"=>[FIELDDATA]])` to add fields
        * Use `$this->forge->addKey('FIELDNAME', true);` to add primary key
        * User `$this->forge->createTable('TABLENAME');` to create the table
* Create a seeder file (to fill initial `user` data)
    * `php spark make:seeder user`
    * File `{TIMESTAMP}_User.php` is created in `app/Database/Seeds/`
    * Use `$this->db->table('TABLENAME')->insert($data);` fo insert a key => value data array
* Run migration
    * `php spart migrate` (It will run all Migrations in order of timestamp)
    *  Table `user` will be created in the database
* Run seeders
    * `php spark db:seed User` (Run seeder `User.php` to insert user data)
* Create models
    * `php spark make:model user`
    * File `app/Models/User.php` will be created
    * Modify `$table` to `user` if you use that name instead of default `users`
* Create controllers
    * `php spark make:controller user`
    * File `app/Controllers/User.php` will be created
    * By default, controllers are created inherited from `app/Controllers/BaseController`
    * To make the controller RESTful, change inheritance to `ResourceController` 
        * `use CodeIgniter\RESTful\ResourceController;`
        * Change `extends BaseController` to `extends ResourceController`
        * Setup the source $modelName
            * `protected $modelName = 'App\Models\User';`
        * Setup the output format (json)
            * `protected $format    = 'json';`
        * For the `index` method return `$this->respond($this->model->findAll());`
* Create routes
    * Modify `app/Config/Routes.php`
    * Add a route for the User controller
        *  Add `$routes->resource('user');` for User controller of type `ResourceController`
* Create Auth controller (authentication)
    * Run `php spark make:controller auth`
    * Modify `app/Controllers/Auth.php` to set a login method
        * Read json POST data
        * Check user email + password on database 
        * If user exists, create session variables, return OK
        * If user does not exist, return Error
    * Modify `app/Controllers/Auth.php` to set a logout method
        * remove session variables
        * return OK
    * Create routes for login and logout in `app/Confir/Routes.php`
        * `$routes->post('/auth/login','Auth::login');`
        * `$routes->get('/auth/logout','Auth::logout');`
* Work with form validations
    * Create validation object 
        * `$validation = \Config\Services::validation();`
    * Add validation rules to login method
        * `$rules = [`
            `'password' => 'required',`
            `'email'    => 'required|valid_email',`
            `];`
        * `$validation->setRules($rules)`
    * Perform validation with `$validation->run($request)`
        * If validation fails, return errors from `validation->getErrors();`
* Setup base page in `app/Config/App.php`
    * To remove `index.php` from URL redirects, set `$indexPage` to empty string
        * `public $indexPage = '';`
* Setup an authentication filter
    * `php spark make:filter auth`
    * File `app/Filters/Auth.php` will be created
    * Edit file on `before` method
        * Check session variables 
            * `$session = session()->get();`
        * Vefify if session login variables exist
            * `if (!@$session['user']){  }`
        * If not logged in, redirect to home page (or login page if exists)
            * `redirect()->to(site_url('')`
    * Edit `app/Config/Filters.php` to declare the new filter in `$aliases`
        * `'auth' => \App\Filters\Auth::class,`
* Setup the filter for a new routing group 
    * Edit `app/Config/Routes` to secure the User controller using the `auth` filter
        * `$routes->group('', ['filter' => 'auth'], static function ($routes) {`
            * `  $routes->resource('user');`
        * `});`
* Working with migrations (rebuild schema)
    * Revert migration changes (or delete the database)
        * To revert each migration: `php spark migrate:rollback`
    * Edit the migration file previously created
        * Example: `app/Database/Migrations/{timestamp}_CreateUser.php`) 
        * Add new fields (eg: `is_admin`) to `$this->forge->addField()`
    * Run the migration again:
        * `php spark migrate`
    * Edit the seeder to add more info
        * Example: (eg: `app/Database/Seeds/User.php`)
        * Include additional data (eg: add `is_admin` value) for new fields created
        * To start with a clean table 
            * Include `$this->db->table('{tablename}')->truncate();` at the beginning
    * Run the seeder again:
        * `php spark db:seed User`
    * To check the table and data inserted successfully
        * Run `php spark db:table TABLENAME` to see the structure and contents of the table    
    * Update related models/controllers 
        * Update Auth Controller to include new info (eg: add `is_admin` to login session data)
* Setup Model
    * Setup allowed fields to process (for create/update)
* Implement Controller (ResourceController)
    * Implement a common JSON result structure (eg: `{"success": {true/false}, "data": [arraydata], "message": "{errormessage}" }`)
    * Methods:
        * `GET` `index`: Get all items
        * `GET` `show/{id}`: Get one item by `id`
        * `POST` `create`: Create one item from request data
        * `PUT` `update/{id}`: Update one item by `id`
        * `DELETE` `delete/{id}`: Delete one item by `id`
    * Using the session user type (eg: `is_admin`) determine limits to access data
        * Add additional data filters (`where()`) when the user is not admin
        * Restrict access to view specific items
        * Restrict specific operations (eg: `delete`, `create`)
        * Restrict specific field changes (eg: `email`)
        * Restrict modifying specific items (eg: other users)
    * Restrict fields to be sent
        * Include only fields available to view on `->select([{fields}])` (eg: remove `password`)
    * Change allowed fields depending on permissions
        * Modify model to implement `addAllowedFields($fields)` or `setAllowedFields($fields)`
        * In controller, modify allowed fields depending on user permissions (eg: add `is_admin` field changes only for admin)
* Additional Auth methods
    * Add `POST` `/auth/password` to allow changing password
    * Add `POST` `/auth/profile` to allow changing profile info
    * Add `GET` `/auth/profile` to get current profile info
* Create helper functions
    * Create `{name}_helper.php` in `App/Helpers/` (eg: `map_helper.php`)
    * Define functions (eg: `mapArrayByKey()`)
    * Use function in code
        * Call `helper('{name}');` (eg: `helper('map');`)
        * Call helper function (eg: `mapArrayByKey(...);`)











## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](http://codeigniter.com).

This repository holds the distributable version of the framework,
including the user guide. It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [the announcement](http://forum.codeigniter.com/thread-62615.html) on the forums.

The user guide corresponding to this version of the framework can be found
[here](https://codeigniter4.github.io/userguide/).


## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Contributing

We welcome contributions from the community.

Please read the [*Contributing to CodeIgniter*](https://github.com/codeigniter4/CodeIgniter4/blob/develop/CONTRIBUTING.md) section in the development repository.

## Server Requirements

PHP version 7.4 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php)
- xml (enabled by default - don't turn it off)
