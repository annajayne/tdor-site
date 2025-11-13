
import os
import subprocess
import sys
import stat
import csv

from datetime import datetime
from ParseDateStr import parse_date
from Report import Report


# Note: this class is very similar to that in ReadTgeuCsvFile.py
# The only difference is in the detailed format of the CSV file - in this case, the CSV files
# admins use to import data to the site (the import file CSV format is very similar to that
# offered as a download, but not identical)
class ImportCsvFileReader:
    def __init__(self):
        self.current_month = 10     # Start in October


    def parse_date(self, value):
        dates = parse_date(value, self.current_month)

        if (len(dates) == 2):
            # Date parsed unambiguously, so update to parsed ISO date - otherwise leave alone
            value = dates[1]

            try:
                dt = datetime.strptime(value, '%Y-%m-%d')
                self.current_month = dt.month
            except ValueError:
                dt = None
        else:
            self.current_month = 0

        return value


    def read(self, pathname):
        f = open(pathname, encoding="utf-8")

        reports = []

        csv_reader = csv.reader(f, delimiter=',', quotechar='"')

        # The first few fields of the import file are listed, but only the "Name" and "Date" columns are needed

        # Name                   - row[0]  - used
        # Age                    - row[1]
        # Birthdate              - row[2]
        # Photo                  - row[3]
        # Photo credit           - row[4]
        # Date                   - row[5]  - used
        # TGEU list ref          - row[6]
        # Address                - row[7]
        # Locality               - row[8]
        # Town/City/Municipality - row[9]
        # State/Province         - row[10]
        # Country          -     - row[11] - used

        name_field = 0
        date_field = 5
        country_field = 11

        for row in csv_reader:
            # print(', '.join(row))

            is_header = (row[0].startswith('\ufeff')) or (row[0] == 'Name') or (row[1] == 'Name')

            if not is_header:
                name = row[name_field].strip()
                if (name == ''):
                    continue

                report = Report()
                reports.append(report)
            
                if (name == 'N.N.') or (name == 'N. N.'):
                    name = 'Name Unknown'
                report.set_name(name)

                date_value = self.parse_date(row[date_field])
                report.set_date(date_value)

                country = row[country_field].strip()
                report.set_country(country)

        f.close()

        return reports
