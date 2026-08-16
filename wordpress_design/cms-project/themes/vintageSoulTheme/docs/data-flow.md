# Data Flow: Adding a New Content Domain

See `ARCHITECTURE.md` for *why* the layering exists. This is the *how* -
the steps to add a new piece of swappable content, using "Gallery Item" as
the placeholder name (replace with your real domain).

1. **Interface** - `src/Repositories/Contracts/GalleryItemRepositoryInterface.php`
   Define the shape you need, nothing more:
   ```php
   interface GalleryItemRepositoryInterface {
       /** @return array<int, array{title:string, image:string}> */
       public function all(): array;
   }
   ```
2. **Repository implementation** - `src/Repositories/JsonGalleryItemRepository.php`
   Reads via `JsonFileProvider::read('data/content/gallery.json')`, maps raw
   rows into exactly the shape the interface promises. No business rules
   here - just "what is a gallery item, structurally."
3. **Data file** - `data/content/gallery.json`. Content only.
4. **Service** - `src/Services/GalleryService.php`, constructed with the
   interface (defaulting to the JSON implementation):
   ```php
   public function __construct(?GalleryItemRepositoryInterface $repo = null) {
       $this->repo = $repo ?? new JsonGalleryItemRepository();
   }
   ```
   Business rules live here (filtering, limits, sorting) - not in the
   Repository, not in the Controller.
5. **Controller** - whichever template context needs it calls
   `(new GalleryService())->method()` and adds the result to its prepared
   data array.
6. **Component** - `components/gallery/grid.php`, rendered via
   `View::component('gallery/grid', $data)`. Receives a plain array, never
   calls the Service or Repository itself.

## Swapping the source later

Write `WpGalleryItemRepository implements GalleryItemRepositoryInterface`,
backed by a custom post type or `WpQueryProvider`. Change step 4's
constructor default to the new class. Steps 5 and 6 - and every existing
caller - are untouched.

## When *not* to do this

If you can't imagine the content ever moving off JSON, and there are no
business rules beyond "read it and show it," a Repository is ceremony
without benefit - read the JSON via `JsonFileProvider` directly from a small
Service (or even the Controller, for something truly trivial) and skip the
interface. Add the interface later if the need for a second source
actually shows up.
