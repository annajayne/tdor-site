import os
import subprocess
import sys
import stat
import csv

from datetime import datetime
from ParseDateStr import parse_date
from ParseDateStr import str_to_datetime
from Report import Report


class TgeuCsvFileReader:
    _tdor_list_ref_prefix = ""


    def __init__(self):
        self.current_month = 10     # Start in October


    def parse_date(self, value):
        dates = parse_date(value, self.current_month)

        if (len(dates) == 2):
            # Date parsed unambiguously
            # First item == original date
            # Second item == date in ISO format
            # => update to parsed ISO date - otherwise leave alone

            # NOTE: This interface needs to be redesigned to omit the original date, returning the parsed ISO date, or None if it cannot be parsed
            # (in which case the caller can decide what to do with the original date)
            value = dates[1]

            try:
                dt = datetime.strptime(value, '%Y-%m-%d')
                self.current_month = dt.month
            except ValueError:
                dt = None
        elif (len(dates) == 1):
            self.current_month = 0
            try:
                dt = str_to_datetime(dates[0])
                value = dt.strftime('%Y-%m-%d') # Removes time fields

                self.current_month = dt.month
            except ValueError:
                self.current_month = 0

        elif (len(dates) == 3):
            self.current_month = 0

        return value


    def read(self, pathname, tdor_list_ref_prefix = ""):
        self._tdor_list_ref_prefix = tdor_list_ref_prefix

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

        # TODO: implement support for geolocation_field
        geolocation_field = -1

        for row in csv_reader:
            # TODO: Make printing optional if a "verbose" flag is set
            #print(', '.join(row))

            is_header = (row[0].startswith('\ufeff')) or (row[0] == 'Name') or (row[1] == 'Name') or (row[0].startswith('Date of the incident'))

            if is_header:
               if row[1].startswith('Name'):
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
                    if row[0].startswith('Date of the incident'):
                        # TGEU internal format
                        date_field = 0
                        country_field = 2
                        city_field = 3
                        geolocation_field = 4 # Not currently used
                        name_field = 5
                        age_field = 15
                        cause_field = 18
                        remarks_field = 21
                        sources_field = 31
                        reported_by_field = 32
            else:
                name = row[name_field].rstrip()
                if (name == ''):
                    continue

                report = Report()
            
                if (name == 'N.N.') or (name == 'N. N.'):
                    name = 'Name Unknown'

                age = row[age_field]

                if (age == "unknown / not applicable") or (age == "not reported"):
                    age = ""

                date_value = self.parse_date(row[date_field])

                tdor_list_ref = self._build_tdor_list_ref(date_value, name)

                city = row[city_field]
                country = row[country_field]

                if (age == 'not reported') or (age == 'unknown'):
                    age = ""

                if country == "United States":
                    country = "USA"

                location = country

                if (city != ''):
                    location = city + ' (' + country + ')'

                report.set_name(name)
                report.set_age(age)
                report.set_date(date_value)
                report.set_tdor_list_ref(tdor_list_ref)
                report.set_location(location)
                report.set_country(country)
                report.set_cause(row[cause_field])

                # TODO(PyTdorLib): Consider merging together into the description field
                report.set_remarks(row[remarks_field])
                report.set_source(row[sources_field])
                report.set_reported_by(row[reported_by_field])

                reports.append(report)

        f.close()

        return reports


    def _build_tdor_list_ref(self, date, name):
        if not self._tdor_list_ref_prefix:
            return ''

        # TODO(PyTdorLib): Reconcile this with ParseDateStr.py
        try:
            dt = datetime.strptime(date, '%Y-%m-%d')        # 2017-11-20
            date_str   = dt.strftime('%d-%b-%Y')            # 20-Nov-2017
        except ValueError:
            dt = None

        return self._tdor_list_ref_prefix + '/' + date + '/' + name
