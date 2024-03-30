# SPECIFICATION FOR OURSITE WEBSITE "OURTASKS"

## Overview

OurTasks is a website that tackles the problem of task managing. It eliminates the need to buy a sticky notes for each task. OurTasks helps the environment by creating a solution to keep track of your tasks. These tasks can also be assigned to multiple people and groups.

The website is targeted towards users with lots of responsibilities. Our audience falls under the ages of 13-99+.

## Team

Stiven Lille - Front-End

Innar Viinamäe - Back-End

Georg Lee - Back-End

## Goals, Objectives and Phases

### Objective

OurTasks aims to simplify task management for users with various responsibilities, providing an eco-friendly alternative to sticky notes. We strive to create an easy-to-use platform promoting efficient and enjoyable task tracking while contributing to environmental sustainability.

### Goals

* Create and design at least 5 pages
* Provide a structure for the initial user experience
* Website has modern styling

### Phases

Milestone #1 - Structure & Design (HTML, CSS)

Milestone #2 - Back-end server-side scripting (PHP)

Milestone #3 - Data and UI interactions (MySQL, JS)

## Content Structure

### Site map

```text
HOME
  +--LOGIN
  |    +--REGISTER
  |    +--MAIN
  |    |    +--ADD FOLDERS
  |    |    +--ACCESS TO FOLDERS
  |    |    |    +--ADD TASKS
  +--REGISTER
  ...
  FOOTER
  +--TERMS AND CONDITIONS
  +--PRIVACY POLICY
```

### Content Types

Index (HOME) - informative, landing page

Login - signing in

Register - signing up

Main - create folders, see those folders, open folders

Folder - task creation, close tasks, view tasks

Privacy policy - informative, legal information

Terms & conditions - informative, legal information

### Page Templates & Design

![Index page](img/specification/index.png)
Index page is the landing page of our website. It has subtle information about why to choose our services. The landing page might be updated in terms of context later on. No mock content.

![Privacy Policy page](img/specification/privacy.png)
Privacy policy page contains legal information about the privacy policy of our website. No mock content.

![Terms & Conditions page](img/specification/terms.png)
Terms & Conditions page contains legal information about the terms and conditions of our website. No mock content.

![Login page](img/specification/login.png)
Login page contains the form to sign into our site to access main. The form currently does nothing and simulates how the login page would look like. The login button currently redirects to Main page without authentication.

![Register page](img/specification/register.png)
Register page contains the form to sign up to our site. The form currently does nothing and simulates how the register process would look like. The password requirements do nothing, but would work in the future.

![Main page](img/specification/main.png)
The main page is the webpage, where users would end up in after signing in. On this page users can see their task folders. Currently there is a personal and group folder. Inside the main page, you can also create folders by clicking the "+" button.

![Folder page](img/specification/folder.png)
The folder page displays your tasks. You can create the tasks with the "+" icon and close those tasks inside the page.


## Functionality

Main - user specific profiles, must be logged in, ability to create folders, ability to look inside folders, access tasks, assign members to folders, access settings

Login - need to have an account before signing in, need to know the password and email

Register - username must be unique, must contain a valid email address, password must be at least 6 characters long, include at least one number and one uppercase letter, password fields must match each other

## Browser Support

* Chrome/Chromium
* Firefox
* mobile browsers (Chrome, Firefox, Safari, etc)

## Hosting

ENOS.
