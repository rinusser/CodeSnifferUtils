# General

This application is a set of PHP\_CodeSniffer rules reflecting personal preferences. As such, the distinction between
what is a feature or a bug is highly subjective.


# Submitting GitHub Issues

### Bugs

Any PHP\_CodeSniffer warnings or errors with these error codes are considered bugs to be fixed:
* `Internal.Exception` (usually with a "processing has been aborted ..." message)
* `*.UnhandledContext` ("unhandled context ..." messages)

Please check the issues list for open entries for this - if not please supply:
* the exact error code (you can enable error codes in the report with `phpcs -s`)
* the relevant parts of your source that trigger the error

### New Features

Ultimately the aim of this phpcs ruleset is to validate my other PHP code. Because of this I avoid introducing any
breaking changes unless I think the new style requirement is an improvement.

If you find a custom sniff that almost matches your style requirements but needs minor adaptions feel free to submit a
feature request: as long as the default settings are backwards compatible new configuration options are welcome.


# Working on Code

### Scope

The upcoming/planned work is managed in the [Issues list](https://github.com/rinusser/CodeSnifferUtils/issues). Each
implemented/fixed GitHub issue corresponds to one commit in `master`, with the issue number (prefixed with `CSU-`) in
the commit message.

### Authorship

There's a lot of existing code in PHP\_CodeSniffer that performs similar tasks already. Instead of copying code from
there please try to extend those existing classes or write entirely new code.

### Code Style

This PHP\_CodeSniffer ruleset is being validated against itself. Test files (in tests/files/) violate the ruleset for
testing purposes. The phpcs.xml configuration file skips these files already.

### Tests

Each custom sniff is covered by tests. Test cases are in tests/cases/ (as phpcs configuration files with a custom
\<expectations\> block), the tested files are in tests/files.

### Validation

Each commit into the `master` branch conforms to this ruleset and passes all tests. Personally I use this command line
to validate the current version:

    ./build.sh && ./phpunit.sh && (phpcs; phpcslive)

### Documentation

Each custom sniff is documented in FEATURES.md. The sniff's documentation shows commented code examples, explains what
the sniff does, how it can be configured and whether found errors are fixable with phpcbf.

### Licensing

Please note that the code is currently licensed under GPLv3 - any contributions are expected to share this license.

I may change the license to a less restrictive open source license at a later date.
