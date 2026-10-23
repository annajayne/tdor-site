# ConvertTgeuTdorData

**ConvertTgeuTdorData** is a Python script which converts TGEU TMM TDoR spreadsheet files to a format compatible with [tdor.translivesmatter.info](tdor.translivesmatter.info) for data checking/comparison purposes.

It is intended to be used in conjunction with ``CompareReportCsvFiles`.

It reads the input (`.xlsx` or `.csv`) file, transforms the data, and writes the output to a new .csv` file. The output format is compatible with those used by [tdor.translivesmatter.info](tdor.translivesmatter.info) for data import and archiving.


## Prerequisites

To run this script you will need:

- A [Python 3.x](https://www.python.org/downloads/) interpreter and the Python libraries the script uses (see below)


The Python libraries used by the script are:

- `colorama` for colored console output
- `pandas` and `openpyxl` for reading Excel spreadsheets


These library dependencies can be installed via the Python `pip` utility using the command line:

```
pip install -r requirements.txt
```



## Command Line Arguments

- `-i` or `--input-file`: The pathname of the input file. `.csv` and `.xslx` files are supported.
- `-o` or `--output-file`: The pathname of the output file.
- `--help` or `-h` : Display help on available commands.


## Usage

To convert the first sheet of a TMM spreadsheet to [tdor.translivesmatter.info](tdor.translivesmatter.info) format, simply run one of the the following commands:

```
python ConvertTgeuTdorData.py --input_file /path/to/input.xlsx --output_file /path/to/output.csv

```

If the spreadsheet has already been converted to CSV you can alternatively use:

```
python ConvertTgeuTdorData.py --input_file /path/to/input.csv --output_file /path/to/output.csv
```

This will provide a concise summary of unique reports in each file without detailed output.
