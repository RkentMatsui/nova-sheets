# Nova Google Sheets Integration

Integrates Google Sheets API to sync partner and employee data from WordPress/WooCommerce.

## Introduction

Nova Google Sheets Integration is a WordPress plugin designed to automate the synchronization of user data between your WordPress site and Google Sheets. It specifically targets partner data and employee emails, ensuring that your spreadsheets are always up-to-date with the latest information from your site.

## Requirements

- **PHP**: 7.4 or higher
- **WordPress**: 5.0 or higher
- **WooCommerce**: Required for order-related syncing
- **Advanced Custom Fields (ACF)**: Used for managing business-specific fields (e.g., `business_id`, `business_name`, `business_type`)
- **Google Cloud Project**: A project with the Google Sheets API enabled and a Service Account credentials JSON file.

## Setup Instructions

1. **Install the Plugin**: Upload the `nova-sheets` folder to your `/wp-content/plugins/` directory and activate it.
2. **Obtain Credentials**: 
   - Create a project in the [Google Cloud Console](https://console.cloud.google.com/).
   - Enable the **Google Sheets API**.
   - Create a **Service Account** and download the private key in **JSON** format.
   - Share your Google Sheet(s) with the email address of the service account.
3. **Configure Settings**:
   - In the WordPress Admin, navigate to **Google Sheets**.
   - **Upload Credentials**: Upload the JSON file you downloaded from the Google Cloud Console.
   - **Spreadsheet IDs**: Enter the Spreadsheet ID for your Partners sheet. You can find this in the URL of your Google Sheet: `https://docs.google.com/spreadsheets/d/SPREADSHEET_ID/edit`.
   - **Employee Emails**: Go to the **Employee Emails** submenu and enter the Spreadsheet ID for your employees sheet.
4. **Save Settings**: Click "Save Settings" on both pages.

## Features

- **Automated Syncing**: The plugin automatically updates the spreadsheet when:
    - A new WooCommerce order is placed.
    - A `nova_quote` post type is saved.
    - A user's role is updated.
    - A user's profile is updated.
- **Manual Updates**: You can manually trigger a full synchronization by clicking the **Update Sheet** button in the plugin settings.
- **Smart Filtering**: Automatically skips entries containing "test" or "demo" to keep your production data clean.
- **Automatic Formatting**: Automatically bolds the header row of the Google Sheet during synchronization.
- **Admin Links**: Quick links in the admin dashboard to view your Google Sheets directly.

## Technical Details

- **Hooks Integration**:
    - `woocommerce_new_order`
    - `save_post_nova_quote`
    - `set_user_role`
    - `profile_update`
- **Classes**:
    - `Nova_Google_Sheets_Integration`: Handles the admin interface and settings.
    - `Nova_Sheets_Client`: Manages communication with the Google Sheets API.
    - `Nova_Partner_Data`: Handles data retrieval for partners and employees.
- **Update Checker**: Includes an integrated update checker that pulls from the GitHub repository.

## Development & Releases

This plugin uses a professional build process via **GitHub Actions**. The `vendor` directory and other build artifacts are excluded from the repository to keep it clean, but they are automatically included in the distributed ZIP files.

### How to Release a New Version

1.  **Update the Version**: Change the `Version:` string in `nova-sheets.php`.
2.  **Commit and Push**:
    ```bash
    git add nova-sheets.php
    git commit -m "Bump version to X.X.X"
    git push origin master
    ```
3.  **Create a Version Tag**: Tags must start with `v` to trigger the build process.
    ```bash
    git tag vX.X.X
    git push origin vX.X.X
    ```
4.  **Wait for Build**: Check the **Actions** tab on GitHub. Once the build is complete, a new **GitHub Release** will be created with a `nova-sheets.zip` file attached.
5.  **WordPress Update**: The integrated update checker will automatically detect the new release and download the pre-built ZIP (including the `vendor` folder).

### Manual Installation (Development)

If you are installing manually from the repository source (not from a Release ZIP), you must run composer to install dependencies:
```bash
composer install
```

## Author

- **Authors**: 
    - Bonn Joel Elimanco <bonnjoel@gmail.com>
    - Rowielokent Matsui <devkentmatsui@gmail.com>
- **Plugin URI**: [http://novasignage.com](http://novasignage.com)
