# Zukit

_Framework serves as the basis for creating plugins or themes for WordPress._

Implements basic functionality for managing scripts and creating plugin or theme settings page based on Gutenberg functionality.

## Install

To _use_ __Zukit__ framework in your project, you need to load its classes before referring to the class that inherits the framework methods. There are many ways to do this, but the easiest one is [to install the framework](https://github.com/picasso/zukit/wiki/%5BMisc%5D-Install) as a `subtree` in your project.

## Documentation

The best way to learn a framework is to look at working examples of its use. This can be done in plugins that I have already adapted for the new framework: [Zu Contact](https://github.com/picasso/zu-contact) и [Zu Media](https://github.com/picasso/zumedia).

Documentation is available on the [GitHub wiki](https://github.com/picasso/zukit/wiki). There I described the main points of working with the framework.


------------------------------------------------------

### Structure of "Zukit"

- Folder __dist__ contains _production_ versions `JS` and `CSS` files;
- Folder __lang__ contains files needed for translations;
- Folder __snippets__ contains a collection of various functions that I have accumulated during my work with WordPress. They are combined into one class for ease of use;
- Folder __traits__ contains traits that are included in the class `zukit_Plugin`. Used to group functionality in a fine-grained and consistent way;
- Folder __src__ contains _source_ versions of `JS` and `CSS` files.

<!--
коды для emoji unicode
https://apps.timwhitlock.info/emoji/tables/unicode

```diff
- red
+ green
! orange
# gray
```
-->

<!-- See [Dmitry Rudakov Coding](https://dmitryrudakov.com/coding/) for complete docs and demos.
-->
