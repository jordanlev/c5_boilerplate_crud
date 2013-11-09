# Boilerplate CRUD (Dashboard Interface for non-page data)
A Concrete5 package that contains a complete working example of a dashboard interface for viewing, editing, adding, and deleting database records (a.k.a. "CRUD"). The data is entirely self-contained in this package... it is not related to any site content (pages, blocks, etc.). In a real-world website, you would have your admin users work with this data in the dashboard, then integrate it yourself with the site content (usually via single_pages or custom blocks).

A high-level explanation is provided below in this README, and the code is commented to explain why things are the way they are. That being said, it might be difficult to understand if you aren't already familiar with other MVC frameworks such as Rails, Kohana, Symfony, CodeIgniter, etc.

_Note that there is an older version of this code which contains less functionality, but is (arguably) easier to understand. You can access that older version by browsing or downloading the "old-style" tag here: [https://github.com/jordanlev/c5_boilerplate_crud/tags](https://github.com/jordanlev/c5_boilerplate_crud/tags) ._

## Features
* Implements the MVC pattern, so data is separated from the markup, and business logic is separated from display logic.
* One controller can have multiple views (a.k.a. `single_pages`), unlike most C5 examples out there which tie one view (`single_page`) to one controller
* Data model has many-to-one and many-to-many relationships
* Data validation rules are declared in the models where they belong (not the controllers)
* Basic validation rules (required, max length, numeric-ness) can be automatically set based on db.xml field definitions
* Controller logic is encapsulated in a separate library so your action methods stay clean and focused on your business logic (not C5's architectural requirements)
* Concrete5 dashboard pages utilize Twitter Bootstrap styles
* Drag-and-drop record sorting
* View helper automatically outputs HTML for "list all records" (with edit/delete buttons for each)
* Robust form validation helpers (including support for custom rules)
* Examples of various complex fields in the "edit" forms (WYSIWYG editor, File Manager file chooser, many-to-many checkbox list, URL Slug guesser)
* Dynamically generated "Config" dashboard page provides editing interface for package-level config settings
* CSRF security tokens included in all forms

## Data Model
The data models a very basic car dealership inventory. The primary entity is `Car`, each of which has a `Body Type` (many-to-one relationship), a `Manufacturer` (many-to-one relationship), and several `Colors` (many-to-many relationship).

## Dashboard Page Structure
There is one dummy controller at the top-level to serve as a placeholder in the dashboard (so we get a top-level dashboard section for our interface). Below that are two controllers -- one for the primary entity (Cars), one for the "lookup list" data (Body Types, Manufacturers, and Colors).

The list of Car records is segmented by Body Type (which acts as a "category" for Cars), so admin users must choose a Body Type before viewing the list of Cars.

The list of Body Types (in the "Misc. Settings" section) can be sorted via drag-and-drop.

## Front-End
One sample front-end `single_page` is provided as a starting point -- but it does not contain any special functionality (because the purpose of this package is to show an example of the back-end dashboard interface).
