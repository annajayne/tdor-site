import glob
import os
import stat
import subprocess
import sys
import unittest

from pathlib import Path

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '../PyTdorLib'))

from Report import Report
from TgeuTextFileReader import TgeuTextFileReader
from TgeuCsvFileReader import TgeuCsvFileReader
from ReportsCsvFileWriter import ReportsCsvFileWriter


def write_csv_file_from_tgeu_txt_file(input_file_pathname, output_file_pathname):
    if (os.path.isfile(output_file_pathname) ):
        os.remove(output_file_pathname)

    print('Reading ' + input_file_pathname)

    reader               = TgeuTextFileReader()
    writer               = ReportsCsvFileWriter()

    tdor_list_ref_prefix = 'tgeu/' + os.path.splitext(os.path.basename(input_file_pathname))[0]
    reports              = reader.read(input_file_pathname, tdor_list_ref_prefix)

    print('Writing ' + csv_file_pathname + '\n')

    return writer.write(reports, output_file_pathname)


def write_csv_file_from_tgeu_csv_file(input_file_pathname, output_file_pathname):
    if (os.path.isfile(output_file_pathname) ):
        os.remove(output_file_pathname)

    print('Reading ' + input_file_pathname)

    reader               = TgeuCsvFileReader()
    writer               = ReportsCsvFileWriter()

    tdor_list_ref_prefix = 'tgeu/' + os.path.splitext(os.path.basename(input_file_pathname))[0]
    reports              = reader.read(input_file_pathname, tdor_list_ref_prefix)

    print('Writing ' + output_file_pathname + '\n')

    return writer.write(reports, output_file_pathname)



if __name__ == '__main__':
    unittest.main(exit=False)

folder = os.path.dirname(os.path.realpath(__file__) ) + '/data'

# For TDoR 2020 and earlier the input files are txt files
txt_file_pathnames = glob.glob(folder + '/*.txt')

for txt_file_pathname in txt_file_pathnames:
    path                = Path(txt_file_pathname)
    csv_file_pathname   = path.with_suffix('').as_posix() + '_processed.csv'

    write_csv_file_from_tgeu_txt_file(txt_file_pathname, csv_file_pathname)


 # For TDoR 2021 onwards both the input and output files are CSV files
input_file_pathnames    = glob.glob(folder + '/*Namelist.csv')

for input_file_pathname in input_file_pathnames:
    path                    = Path(input_file_pathname)
    output_file_pathname    = path.with_suffix('').as_posix() + '_processed.csv'

    write_csv_file_from_tgeu_csv_file(input_file_pathname, output_file_pathname)
