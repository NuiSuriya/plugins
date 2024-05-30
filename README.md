# Overview

This repository contains two WordPress plugins developed to handle specific tasks related to post management and custom API endpoints.

1. **XML Import Plugin**: This plugin imports XML files to upload post data to the WordPress database with a custom post type.
2. **Custom Endpoints Plugin**: This plugin generates four custom endpoints:
   - Show all of an author's published posts.
   - Update a post's status between the standard WordPress statuses.
   - Remove an author and reassign their posts to one or more different authors.
   - Publish a new post for a specified author.

## Installation

1. Create a new WordPress instance.
2. Clone this repository to replace the plugin folder:
   ```bash
   git clone https://github.com/NuiSuriya/plugins.git wp-content/plugins/

3. Activate both plugins through the 'Plugins' menu in WordPress.

## Steps Taken

1. Studied the data and data structures from the provided database.
2. Attempted to import data to WordPress using the built-in Tool.
3. Discovered that the post type in the data is 'pagw' instead of 'page', which prevented the use of the built-in tool.
4. Decided to create a new plugin to import the file.
5. Investigated the file and syntax to extract the required data.
6. Developed the XML Import Plugin.
7. Investigated and studied the WordPress REST API and how to write it in code.
8. Planned to create plugin pages to improve user experience.
9. Started developing Custom REST API and testing the result step by step using Postman.
10. Used GitHub Copilot to find the right syntax, code, and ideas to achieve the assignment.

## New Step Plan

Create two more subpages for the user to get the result of the next two endpoints:
  - Remove an author and reassign their posts to one or more different authors.
  - Publish a new post for a specified author.

