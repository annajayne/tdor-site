import re

from colorama import Fore, Style
from rapidfuzz import fuzz

from ParseDateStr import str_to_datetime
from Report import Report


class ReportCorrelator:
    _reports1 = []
    _reports2 = []
    _verbose = False


    def __init__(self, reports1, reports2, verbose = False):
        self._reports1 = reports1
        self._reports2 = reports2
        self._verbose = verbose

        if verbose:
            print(f"\nReportCorrelator created, file1 has {len(reports1)} reports; file2 has {len(reports2)} reports")


    # Very basic screening to identify reports that are unique to each collection
    def identify_unique_reports(self):
        # Create a set of tuples representing the Report objects in each collection
        # For simplicity we just index on name, date and country
        set1 = {(r.get_name(), r.get_date(), r.get_country()) for r in self._reports1}
        set2 = {(r.get_name(), r.get_date(), r.get_country()) for r in self._reports2}

        # Identify the elements present in one set but not the other
        unique1 = set1 - set2
        unique2 = set2 - set1

        return sorted([r for r in self._reports1 if (r.get_name(), r.get_date(), r.get_country()) in unique1], key=lambda r: r.get_date()), \
               sorted([r for r in self._reports2 if (r.get_name(), r.get_date(), r.get_country()) in unique2], key=lambda r: r.get_date())


    def identify_unique_reports_fuzzy(self, fuzz_threshold):
        unique_reports1, unique_reports2 = self.identify_unique_reports()

        if (fuzz_threshold >= 1.0):
            return unique_reports1, unique_reports2

        matched_reports1 = []
        matched_reports2 = []

        if self._verbose:
            print("")

        for report1 in unique_reports1:
            for report2 in unique_reports2:
                if report1 in matched_reports1:
                    continue

                if report2 in matched_reports2:
                    continue

                # Precheck - if the reports are in different countries, they can't be the same case unless the data is very wrong
                if (report1.get_country() != report2.get_country()):

                    if self._verbose:
                        print(Style.DIM + f"The following reports are different cases as they are in different countries:")
                        print("    " + Report.get_report_summary(report1))
                        print("    " + Report.get_report_summary(report2))
                        print(Style.RESET_ALL)

                else:
                    # Compute a similarity score between the two reports
                    sim = self._compute_similarity(report1, report2)

                    if (sim > fuzz_threshold):
                        if self._verbose:
                            print(Fore.LIGHTGREEN_EX + f"The following reports are likely the same case [similarity: {sim:.2f}]:")
                            print("    " + Report.get_report_summary(report1))
                            print("    " + Report.get_report_summary(report2))
                            print(Style.RESET_ALL)

                        matched_reports1.append(report1)
                        matched_reports2.append(report2)

                    else:
                        if self._verbose:
                            print(Style.DIM + f"The following reports are likely different cases [similarity: {sim:.2f}]:")
                            print("    " + Report.get_report_summary(report1))
                            print("    " + Report.get_report_summary(report2))
                            print(Style.RESET_ALL)

        for report1 in matched_reports1:
            unique_reports1.remove(report1)

        for report2 in matched_reports2:
            unique_reports2.remove(report2)

        return unique_reports1, unique_reports2


    def _normalise(self, s):
        s = s.lower()
        s = re.sub(r"[^\w\s]", "", s)

        return " ".join(s.split())


    def _compute_similarity(self, report1, report2):
        name_score = fuzz.WRatio(self._normalise(report1.get_name()),
                                 self._normalise(report2.get_name())) / 100

        # Might want to use WRatio() here instead
        location_score = fuzz.WRatio(self._normalise(report1.get_location()),
                                               self._normalise(report2.get_location())) / 100

        report1_date =  str_to_datetime(report1.get_date())
        report2_date =  str_to_datetime(report2.get_date())

        delta_days = abs((report1_date - report2_date).days)
        date_score = max(0, 1 - delta_days / 30)

        return 0.5 * name_score + 0.3 * location_score + 0.2 * date_score
