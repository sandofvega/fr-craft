# Craft Plugin List

This program give you a list of all `craft-plugin` composer packages from [packagist.org](https://getcomposer.org/).

## Requirements

* [PHP](https://www.php.net/) >= 8.0
* [Composer](https://getcomposer.org/) >= 2.0

## Installation

1. Clone the program from github to the desire directory. And `cd` to that directory.

2. Make the program executable:

```bash
chmod +x craft-plugin-list
```

3. Create a symlink to run from anywhere:

```bash
sudo ln -s $(realpath craft-plugin-list) /usr/local/bin
```

## Options

* To see all the available options:

```bash
craft-plugin-list --help
```

### Limit

Set a output limit, use `--limit`.

* _Default:_ 50

Example:

```bash
craft-plugin-list --limit=5
```

### orderBy

To set order/sort by , use `--orderBy`.

* _Available parameters:_ `downloads`, `favers`, `dependents`, `testLibrary`, `updated`

* _Default:_ `downloads`

* Parameters are **case-sensitive**.

Example:

```bash
craft-plugin-list --orderBy=favers
```

### order

To set sorting order, use `--order`.

* _Available parameters:_ `ASC`, `DESC`

* _Default:_ `DESC`

* Parameters are **not** case-sensitive.

Example:

```bash
craft-plugin-list --order=ASC
```

### output

To set output file path, use `--output`.

* Must be a JSON (.json) file.

Example:

```bash
craft-plugin-list --output=output_file.json
```

## Note

* If you set a bigger number in the `limit` option. Then try to run the program with VPN, because [packagist.org](https://getcomposer.org/) may block your IP.
