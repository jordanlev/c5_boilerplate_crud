# Boilerplate CRUD (Dashboard Interface for non-page data)
Sample starter code for custom dashboard interfaces (for data that isn't page-specific... that is, where page attributes aren't enough).
Uses the example of a car dealership (keeps track of the inventory of available cars -- not sales though).

## Entity Model and Controller layout
Car is the primary model. Each car must have 1 body type (one-to-many rltnshp), 1 manufacturer (one-to-many rltnshp), and may have 0 or more colors (many-to-many rltnshp).
After talking with the client and understanding their usage needs, we realize that the Car entity is the one that will be edited most frequently. So we will give Cars its own controller / dashboard page, and then put all the other entities (which are not edited frequently... they're basically lookup list data) into one "misc" controller. Our package will have 1 top-level section in the dashboard menu, with 2 pages in it -- cars and misc. Since we want 2 separate dashboard pages, we should have 2 separate controller files. (Note that we have many more view files than this, because each controller deals with a variety of actions, each with their own view -- also, within "Misc" there are many sub-pages (several pages for each "misc." entity). This is ok (desirable even!).
One caveat: must have a view.php file to correspond with each controller php file... even if the view isn't used (it messes up package installation though if C5 can't find an actual view.php file under the appropriate directory of "single_pages"). In our example, we're actually adding a third controller that just serves as a placeholder for the c5 dashboard menu (so our real pages are navigable from the dashboard menu)... and this controller must have a corresponding view.php file, even though there is nothing to view (the only thing that happens is you get redirected to the cars controller).

## Features represented by each model
Body Types employs sortable-ness

Car is primary model, and is always filtered through a specific body type.
	Also contains photo/file, rich text/wysiwyg, and price/float field examples

Manufacturers are another related record to Car (many-to-one), but is not used to filter cars (like body_types is).
	Also contains boolean/checkbox field example

Colors has a many-to-many relationship with cars

