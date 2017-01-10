# # Personal Cloud API
The personal cloud API is a small Lumen application that allows file uploads (excluding disallowed extensions). Files are stored in a private section of the application, and indexed by the database.

## Getting Started
To begin using the Personal Cloud API, do the following:
1. Clone the repository to your own server
2. Ensure proper write permissions on `storage/app` which files are kept.
3. Set up your `.env` file to point to your database of choice (standard Laravel database drivers are included in Lumen).
4. Run `php artisan migrate` and ensure you get a positive result
5. Test out file uploads at `servername/upload`

## To use the API from a Mobile App / WebApp / Single Page App (SPA) / Toaster or Fridge
Once you’re all set, you can send a `multipart/form-data` `POST` request to `/api/v1/file`. The endpoint only accepts one parameter, `file` which should be the actual file. Upon success, you will receive a JSON response with a `location` string node with the publicly accessible URL of the file.

Hitting that publicly accessible URL will return the file, with the original filename as when it was uploaded.

To restrict certain filetypes, there is a `disallowedExtensions` array belonging to the `FilesController` class. Those extensions will result in an HTTP error 415.

You can customize the messaging of the different type of errors by checking the `__constructor` of `FilesController`.

If the file is too large, or other PHP-related configuration issues, you will get a Lumen-provided `500` error and a rather vulgar error message (you’re welcome…). Check your `php.ini` file for common snafus like `upload_max_filesize` and `post_max_size`.


## License
The API is presently unlicensed. Any use is at your own risk.

## See Also
Coming soon… Android Sample App, iOS Sample App, React Sample App. And hey, maybe Angular too.
