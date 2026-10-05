# Changelog

All notable changes to this extension are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.2.28] - 2026-10-05

### Fixed
- Generated HTML is now cleaned on the server and in the browser before it reaches the Page Builder stage or an editor. Event attributes without quotes (for example `onerror=`), `javascript:` links, SVG, comments and unclosed script tags were previously let through and could run in the admin.
- The Page Builder "AI Content" confirmation message now has an Undo button that restores the stage content from before the generation.
- The "Generate Full Page Content with AI" dialog works with the keyboard and screen readers: it is announced as a dialog, every control has a label, focus moves into the dialog and back to the toolbar button when it closes, Tab stays inside the dialog, Escape closes only the dialog, and status and result messages are announced. An empty prompt is no longer sent.
- The page title is placed into the default prompt as text instead of HTML.
- The saved prompt list printed into the admin page is escaped so a prompt cannot end the script block.
- Configuration: Temperature must be a number from 0 to 1 and Max Tokens must be greater than 0. The API key links open with `rel="noopener noreferrer"`.
