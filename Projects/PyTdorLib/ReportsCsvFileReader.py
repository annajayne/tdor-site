import csv
import os
import stat
import subprocess
import sys

sys.path.append('../')

from datetime import datetime
from ParseDateStr import parse_date
from Report import Report

# Class to read CSV files in native tdor.translivesmatter.info data format (i.e. those used to download data
# from tdor.translivesmatter.info, or those archived in the tdor-data repo - the latter of which can be used
# by admins to import data to tdor.translivesmatter.info).
#
# This class is similar in outline to that in TgeuCsvFileReader.py. The main difference is in the detailed
# format of the CSV file.
 
class ReportsCsvFileReader:
    # Native TDoR data CSV files are those associated with either downloads from tdor.translivesmatter.info or
    # CSV files archived in the tdor-data repo (the latter of which can also be used to import data to the site).
    # 
    # These can contain the following fields:
    #
    #   Name                            - supported
    #   Age                             - supported
    #   Birthdate
    #   Photo
    #   Photo source
    #   Thumbnail
    #   Date                            - supported
    #   TDoR list ref
    #
    #   Either (see note 1):
    #
    #       Location                    - supported
    #
    #   OR:
    #
    #       Address
    #       Locality
    #       Town/City/Municipality      - supported
    #       State/Province              - supported
    #
    #   Country                         - supported
    #
    #   Optional (see note 2):
    #
    #       Country Code
    #
    #       Latitude
    #       Longitude
    #       Category                    - supported
    #       Cause of death              - supported
    #       Description                  -supported
    #       Tweet
    #       Permalink                   - supported (see Note 3)
    #
    #   Optional (see note 2):
    #
    #       QR code
    #
    # Notes:
    #   1.  Older versions of the native CSV format contain a "Location field". Newer ones include Address, Locality, Town/City/Municipality and State/Province fields instead.
    #   2.  These fields are only provided in files downloaded from the site rather than those archived in the tdor-data repo.
    #   3.  If provided, the permalink is used to determine the uid of the report.

    _name_field = 0
    _age_field = 1
    _date_field = -1
    _tdor_list_ref_field = -1
    _location_field = -1
    _city_field = -1
    _state_field = -1
    _country_field = -1
    _latitude_field = -1
    _longitude_field = -1
    _category_field = -1
    _cause_field = -1
    _description_field = -1
    _permalink_field = -1

    _current_month = 10  # TDoR periods start in October. This isn't a good fit for all files, but will be while checking data related to a specific TDoR period.


    def read(self, pathname):
        f = open(pathname, encoding="utf-8")

        reports = []

        csv_reader = csv.reader(f, delimiter=',', quotechar='"')

        for row in csv_reader:
            if self._is_header_row(row):
                # Read header names & infer indces
                self._map_header_row(row)
            else:
                report = self._map_report_data(row)
                if report:
                    reports.append(report)

        f.close()

        return reports


    # Implementation

    def _is_header_row(self, row):
        return (row[0].startswith('\ufeff')) or (row[0] == 'Name') or (row[1] == 'Name')

    
    def _map_header_row(self, row):
        # Read header names & infer indces
        if '\ufeffName' in row:
            self._name_field = row.index('\ufeffName')
        else:
            self._name_field = row.index('Name')

        self._age_field = row.index('Age')
        self._date_field = row.index('Date')

        if 'TDoR list ref' in row:
            self._tdor_list_ref_field = row.index('TDoR list ref')

        if 'Town/City/Municipality' in row:
            self._city_field = row.index('Town/City/Municipality')

        if 'State/Province' in row:
            self._state_field = row.index('State/Province')
        
        if 'Location' in row:
            self._location_field = row.index('Location')

        self._country_field = row.index('Country')

        if 'Latitude' in row:
            self._latitude_field = row.index('Latitude')

        if 'Longitude' in row:
            self._longitude_field = row.index('Longitude')

        if 'Category' in row:
            self._category_field = row.index('Category')
      
        if 'Cause of death' in row:
            self._cause_field = row.index('Cause of death')

        if 'Description' in row:
            self._description_field = row.index('Description')

        if 'Permalink' in row:
            self._permalink_field = row.index('Permalink')


    def _map_report_data(self, row):
        name = self._get_name_from_row(row)

        if (name == ''):
            return None

        age = row[self._age_field].strip()
        date_value = self._parse_date(row[self._date_field])

        if self._tdor_list_ref_field >= 0:
            tdor_list_ref = row[self._tdor_list_ref_field].strip()

        location = self._get_location_from_row(row)
        country = row[self._country_field].strip()
        latitude = row[self._latitude_field].strip()
        longitude = row[self._longitude_field].strip()
        category = row[self._category_field].strip()
        cause = row[self._cause_field].strip()
        description = row[self._description_field].strip()
        permalink = row[self._permalink_field].strip()
        uid = self._get_uid_from_permalink(permalink)

        report = Report()

        # TODO(PyTdorLib): add support for:
        #
        #   - Birthdate
        #   - Photo
        #   - Photo source
        #   - Thumbnail [optional]
        #   - Address [optional]
        #   - Locality [optional]
        #   - Tweet
        #   - QR code [optional]
        #
        # TODO(PyTdorLib): rename remarks to description in the Report class

        report.set_name(name)
        report.set_age(age)
        report.set_date(date_value)
        report.set_tdor_list_ref(tdor_list_ref)
        report.set_location(location)
        report.set_country(country)
        report.set_latitude(latitude)
        report.set_longitude(longitude)
        report.set_category(category)
        report.set_cause(cause)
        report.set_description(description)
        report.set_permalink(permalink)
        report.set_uid(uid)

        return report


    def _get_name_from_row(self, row):
        name = row[self._name_field].strip()
        if not name or (name == 'N.N.') or (name == 'N. N.'):
            return 'Name Unknown'

        return name.strip()


    def _parse_date(self, value):
        dates = parse_date(value, self._current_month)

        if (len(dates) == 2):
            # Date parsed ambiguously, so update to parsed ISO date - otherwise leave alone
            value = dates[1]

            try:
                dt = datetime.strptime(value, '%Y-%m-%d')
                self._current_month = dt.month
            except ValueError:
                dt = None
        else:
             # Date parsed unambiguously
             self._current_month = 0

        return value


    def _get_location_from_row(self, row):
        if (self._city_field >= 0 and len(row[self._city_field]) > 0):
            return f"{row[self._city_field]}, {row[self._state_field]}"
        elif self._state_field >= 0:
            return row[self._state_field]

        return row[self._location_field].strip()


    def _get_uid_from_permalink(self, permalink):
        if permalink:
            return permalink.split('_')[-1]

        return None