import argparse
import os
import pandas
import sys

from colorama import Fore, Style

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '../PyTdorLib'))

from TgeuCsvFileReader import TgeuCsvFileReader
from ReportsCsvFileWriter import ReportsCsvFileWriter


def run():
    print(Fore.LIGHTGREEN_EX + "ConvertTgeuTdorData\n" + Style.RESET_ALL)

    parser = argparse.ArgumentParser(description='This script converts TDoR TDoR data files to a format compatible with tdor.translivesmatter.info for data checking/comparison purposes. It is intended to be used in conjunction with ' + Style.BRIGHT + 'CompareReportCsvFiles' + Style.NORMAL)

    parser.add_argument('-i','--input-file', help = 'The path of the input CSV file', required=True)
    parser.add_argument('-o','--output-file', help = 'The path of the output CSV file', required=True)
    
    parser._actions[0].help = 'Show this help message and exit'

    try:
        args = parser.parse_args()
    except:
        exit(0)

    print(f"Input file: {args.input_file}")
    print(f"Output file: {args.output_file}")

    if not os.path.exists(args.input_file):
        print(f"\nERROR: {args.input_file} not found")
        exit(1)

    if os.path.exists(args.output_file):
        print('Deleting existing ' + args.output_file)
        try:
            os.remove(args.output_file)
        except Exception as e:
            print(f"\nERROR: Unable to delete the file {args.output_file}. Details: {e}")
            exit(1)

    basename, extension = os.path.splitext(args.input_file)

    input_csv_path = args.input_file

    if extension == '.xlsx':
        # The input file is an Excel spreadsheet - before we can process it we must convert it to a .csv file
        try:
            data_frame = workbook = pandas.read_excel(args.input_file, sheet_name = 0)

            input_csv_path = basename + ".csv"

            if os.path.exists(input_csv_path):
               print('Deleting existing ' + input_csv_path)
               os.remove(input_csv_path)

            # Note: the output file must be UTF-8 with a leading BOM (Byte Order Mark) to ensure that Excel
            # can open it correctly. We can use the encoding "utf_8_sig" to ensure this.
            data_frame.to_csv(input_csv_path, encoding="utf_8_sig", index=False)

            print(f"Created {input_csv_path} from the first sheet of {args.input_file}")
        except Exception as e:
            print("Error: Unable to read the Excel file. Make sure it is a valid Excel file with multiple sheets."
                  f" Details: {e}")
            exit(1)

    elif (extension != '.csv'):
        print(Fore.LIGHTRED_EX + f"ERROR: {args.input_file} must be a .csv or .xlsx file" + Style.RESET_ALL)
        exit(1)

    reader = TgeuCsvFileReader()

    print('Reading ' + input_csv_path)

    reports = reader.read(input_csv_path)

    print("")
    print(f"{len(reports)} reports read from {input_csv_path}")

    writer = ReportsCsvFileWriter()

    writer.write_file(reports, "tmm", args.output_file)

    print (Fore.LIGHTGREEN_EX +f"{len(reports)} reports written to {args.output_file}\n"  + Style.RESET_ALL)


if __name__ == "__main__":
    run()
