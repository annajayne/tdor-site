
import os
import subprocess
import sys
import stat
import csv

from datetime import datetime
from ParseDateStr import parse_date
from Report import Report



class TgeuCsvFileReader:
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

        for row in csv_reader:
            print(', '.join(row))

            # Fields:

            # Name              - row[0] 
            # Age               - row[1] 
            # Occupation        - row[2]      (not currently used)
            # Date of death     - row[3]
            # City              - row[4]
            # Country           - row[5]
            # Location of death - row[6]      (not currently used)
            # Cause of death    - row[7]
            # Remarks           - row[8]      Maps to "Description"
            # Observaciones     - row[9]      (not currently used)
            # Sources           - row[10]     Appended to remarks

            if (row[0] != '\ufeffName'):                            # Skip the header
                report = Report()
                reports.append(report)
            
                name = row[0]
                if (name == 'N.N.'):
                    name = 'Name Unknown'
                report.set_name(name)

                if (row[1] != 'not reported'):
                    report.set_age(row[1])
        
                date_value = self.parse_date(row[3])
                report.set_date(date_value)

                location = ''
                if (row[4] != ''):
                    location = row[4] + ' (' + row[5] + ')'
                else:
                    location = row[4]

                report.set_location(location)
                #report.set_city(row[4])
                #report.set_country(row[5])
                report.set_cause(row[7])
                report.set_remarks(row[8])
                report.set_source(row[10])

        f.close()

        return reports
