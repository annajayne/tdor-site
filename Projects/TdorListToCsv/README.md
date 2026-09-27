# TdorListToCsv

`TdorListToCsv` is a Python script which converts Transgender Europe (TGEU) TDoR lists (in their various formats) to a form suitable for importing into the **[tdor.translivesmatter.info](https://tdor.translivesmatter.info)** site.

The script runs without parameters, taking input from the `data` folder and writing generated `.csv` files to the same folder.


## Prerequisites

- A [Python 3.x](https://www.python.org/downloads/) interpreter.

Note that although the script also uses the files in the PyTdorLib` directory, no additional Python libraries need be installed.


## Input formats

Originally, Transgender Europe (TGEU) released TDoR lists in PDF form every November.

A selection of such files - converted to text file form are included, for example `TvT_TMM_TDoR2018_Namelist_EN.txt`. These files are freeform text so parsing them is a bit of an art, to say the least.

From 2021 onwards data was released in a more rich form in `.csv` files (which can be opened by a spreadsheet app) and have a regular format which can be transformed easily.


## Output format

`TdorListToCsv` generates a output file for each input file (`.csv` or .`txt`) found. The output is a `.csv` file directly suitable for archival purposes or for uploading directly to [tdor.translivesmatter.info](https://tdor.translivesmatter.info).

e.g.:

- for `TvT_TMM_TDoR2018_Namelist_EN.txt`, the output file is 
``TvT_TMM_TDoR2018_Namelist_EN_processed.csv`

- for `2025-TMM-Namelist.csv` the output file is `2025-TMM-Namelist_processed.csv`


The output `.csv` files include the following fields:

- **Name** - taken directly from the input file
- **Age** - taken directly from the input file
- Birthdate - left blank
- Photo - left blank
- Photo source - left blank
- **Date** - taken directly from the input file
- **TDoR list ref** - calculated
- Address - left blank
- Locality - left blank
- **Town/City/Municipality** - taken directly from the input file
- **State/Province** - taken directly from the input file
- **Country** - taken directly from the input file
- Latitude - left blank
- Longitude - left blank
- **Category** - calculated
- **Cause** - taken directly from the input file
- **Description** - taken directly from the input file
- Tweet - left blank
- Permalink - left blank


## Unit tests

Unit tests are provided which verify that the expected number of cases are read for each year and that selected properties are correct.

The test should be expanded whenever new input data is added.
