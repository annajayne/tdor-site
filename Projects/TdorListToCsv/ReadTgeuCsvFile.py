
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

        # Default fields:

        # TDoR 2021-23:
        #
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
        # Reported by       - row[11]     Appended to remarks

        name_field = 0
        age_field = 1
        date_field = 3
        city_field = 4
        country_field = 5
        cause_field = 7
        remarks_field = 8
        sources_field = 10
        reported_by_field = 11

        for row in csv_reader:
            print(', '.join(row))

            is_header = (row[0].startswith('\ufeff')) or (row[0] == 'Name') or (row[1] == 'Name')

            if (is_header):
               if (row[1] == 'Name'):
                    # TDoR 2024:
                    #
                    # (no title)        - row[0]      (not currently used)
                    # Name              - row[1] 
                    # Age               - row[2] 
                    # Occupation        - row[3]      (not currently used)
                    # Date of death     - row[4]
                    # City              - row[5]
                    # Country           - row[6]
                    # Location of death - row[7]      (not currently used)
                    # Cause of death    - row[8]
                    # Remarks           - row[9]      Maps to "Description"
                    # Observaciones     - row[10]     (not currently used)
                    # Reported by       - row[11]     Appended to remarks
                    # Sources           - row[12]     Appended to remarks

                    name_field = 1
                    age_field = 2
                    date_field = 4
                    city_field = 5
                    country_field = 6
                    cause_field = 8
                    remarks_field = 9
                    reported_by_field = 11
                    sources_field = 12
            else:
                name = row[name_field].rstrip()
                if (name == ''):
                    continue

                report = Report()
                reports.append(report)
            
                if (name == 'N.N.') or (name == 'N. N.'):
                    name = 'Name Unknown'
                report.set_name(name)

                age = row[age_field]
                if (age != 'not reported') and (age != 'unknown'):
                    report.set_age(age)
        
                date_value = self.parse_date(row[date_field])
                report.set_date(date_value)

                location = ''
                city = row[city_field]
                country = row[country_field]

                if (city != ''):
                    location = city + ' (' + country + ')'
                else:
                    location = country

                report.set_location(location)
                #report.set_city(city)
                #report.set_country(country)
                report.set_cause(row[cause_field])
                report.set_remarks(row[remarks_field])
                report.set_source(row[sources_field])
                report.set_reported_by(row[reported_by_field])

        f.close()

        return reports
