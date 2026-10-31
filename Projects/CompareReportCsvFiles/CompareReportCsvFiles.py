import argparse
import os
import sys

from colorama import Fore, Style

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '../PyTdorLib'))

from NativeCsvFileReader import NativeCsvFileReader
from TgeuCsvFileReader import TgeuCsvFileReader
from Report import Report
from ReportCorrelator import ReportCorrelator


def run():
    print(Fore.LIGHTGREEN_EX + "CompareReportCsvFiles" + Style.RESET_ALL)

    parser = argparse.ArgumentParser(description='This script comnpares CSV report files, identifying cases in one but not the other')

    parser.add_argument('-f1','--input-file1', help = 'The path of the first input file', required=True)
    parser.add_argument('-f2','--input-file2', help = 'The path of the first input file', required=True)
    parser.add_argument('-v','--verbose', action="store_true", help = 'Displays verbose output')
    parser.add_argument('-z','--fuzz-threshold', type=float, default=0.7, help = 'Set the threshold (0 to 1) for fuzzy matching. The default value is 0.7')
    
    parser._actions[0].help = 'Show this help message and exit'

    try:
        args = parser.parse_args()
    except:
        exit(0)

    if not os.path.exists(args.input_file1):
        print(f"ERROR: {args.input_file1} not found")
        exit(1)

    if not os.path.exists(args.input_file2):
        print(f"ERROR: {args.input_file2} not found")
        exit(1)

    if (args.fuzz_threshold) < 0 or (args.fuzz_threshold > 1):
        print("ERROR: fuzz_threshold must be between 0 and 1")
        exit(1)

    print(f"Input file 1: {args.input_file1}")
    print(f"Input file 2: {args.input_file2}")

    reader = NativeCsvFileReader() # Assumes tdor.translivesmatter.info format

    print('Reading ' + args.input_file1)

    reports1 = reader.read(args.input_file1)

    if args.verbose:
        for report in reports1:
            print("  " + Report.get_report_summary(report))

    print(f"{len(reports1)} reports read")

    print('Reading ' + args.input_file2)

    reports2 = reader.read(args.input_file2)

    if args.verbose:
        for report in reports2:
            print("  " + Report.get_report_summary(report))

    print(f"{len(reports2)} reports read")

    correlator = ReportCorrelator(reports1, reports2, args.verbose)

    if (args.fuzz_threshold < 1):
        correlator.identify_unique_reports_fuzzy(args.fuzz_threshold)
    else:
        correlator.identify_unique_reports()
    
    unique_reports1, unique_reports2 = correlator.get_unique_reports()

    print(Style.BRIGHT + f"{len(unique_reports1)} reports likely unique to {args.input_file1}" + Style.RESET_ALL)
    print("")

    for report in unique_reports1:
        print(Fore.CYAN + "  " + Report.get_report_summary(report) + Fore.RESET)

    print("")

    print(Style.BRIGHT + f"{len(unique_reports2)} reports likely unique to {args.input_file2}" + Style.RESET_ALL)

    print("")
    for report in unique_reports2:
        print(Fore.CYAN + "  " + Report.get_report_summary(report) + Fore.RESET)

    print("")


if __name__ == "__main__":
    run()
