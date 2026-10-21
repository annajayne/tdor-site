# CompareReportCsvFiles

**CompareReportCsvFiles** is a Python script that compares two tdor.translivesmatter.info CSV case report files, identifying cases present in one file but not the other.

This tool can be particularly useful for reviewing and verifying data collated from various sources.


## Prerequisites

To run this script you will need:

- A [Python 3.x](https://www.python.org/downloads/) interpreter and the Python libraries the script uses (see below)
- The `colorama` library for colored console output
- The `rapidfuzz` library for fuzzy matching of reports


## Supported CSV File Formats

Supported CSV report file formats include:

- That produced by the "Download Data" link at [tdor.translivesmatter.info/reports](tdor.translivesmatter.info/reports) 
- The simpler format used for tdor.translivesmatter.info case data archival in the [tdor-data repository](https://github.com/annajayne/tdor-data).


## Command Line Arguments

- `--input` or `-i`: Specifies the pathname of a PowerPoint document containing a single slide per victim (as downloaded from [https://tdor.translivesmatter.info/reports](https://tdor.translivesmatter.info/reports)).
- `-f1` or `--input-file1`: The pathname of the first input file.
- `-f2` or `--input-file2`: The pathname of the second input file.
- `-v` or `--verbose`: Displays verbose output, providing more detailed information about the comparison process.
- `-z` or `--fuzz-threshold`: Sets the threshold (0 to 1) for fuzzy matching. The default value is 0.7; a value of 1.0 disables fuzzy matching entirely.
- `--help` or `-h` : Display help on available commands.


## Usage

To compare two CSV files, such as `file1.csv` and `file2.csv`, with a fuzziness threshold of 0.9 and verbose output:

```
python CompareReportCsvFiles.py --input-file1 ./file1.csv --input-file2 ./file2.csv -verbose --fuzz-threshold 0.9
```

This will provide a concise summary of unique reports in each file without detailed output.
