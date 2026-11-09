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
    #   Birthdate                       - supported
    #   Photo                           - supported
    #   Photo source                    - supported
    #   Thumbnail
    #   Date                            - supported
    #   TDoR list ref                   - supported
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
    #       Latitude                    - supported
    #       Longitude                   - supported
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
    _birthdate_field = -1
    _photo_filename_field = -1
    _photo_source_field = -1
    _date_field = -1
    _tdor_list_ref_field = -1
    _location_field = -1
    _address_field = -1
    _locality_field = -1
    _municipality_field = -1
    _state_field = -1
    _country_field = -1
    _latitude_field = -1
    _longitude_field = -1
    _category_field = -1
    _cause_field = -1
    _description_field = -1
    _post_text_field = -1
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
        self._name_field = self._get_field_index(row, "Name")
        if self._name_field < 0:
            self._name_field = self._get_field_index(row, "\ufeffName")

        self._age_field = self._get_field_index(row, "Age")
        self._birthdate_field = self._get_field_index(row, "Birthdate")
        self._photo_filename_field = self._get_field_index(row, "Photo")
        self._photo_source_field = self._get_field_index(row, "Photo source")
        self._date_field = self._get_field_index(row, "Date")
        self._tdor_list_ref_field = self._get_field_index(row, "TDoR list ref")
        self._address_field = self._get_field_index(row, "Address")
        self._locality_field = self._get_field_index(row, "Locality")
        self._municipality_field = self._get_field_index(row, "Town/City/Municipality")
        self._state_field = self._get_field_index(row, "State/Province")
        self._location_field = self._get_field_index(row, "Location")
        self._country_field = self._get_field_index(row, "Country")
        self._latitude_field = self._get_field_index(row, "Latitude")
        self._longitude_field = self._get_field_index(row, "Longitude")
        self._category_field = self._get_field_index(row, "Category")
        self._cause_field = self._get_field_index(row, "Cause of death")
        self._description_field = self._get_field_index(row, "Description")
        self._post_text_field = self._get_field_index(row, "Tweet")
        self._permalink_field = self._get_field_index(row, "Permalink")


    def _get_field_index(self, row, title):
        if title in row:
            return row.index(title)

        return -1;


    def _get_field_value(self, row, field_index):
        if field_index >= 0:
            return row[field_index].strip()

        return ""


    def _map_report_data(self, row):
        name = self._get_name_from_row(row)

        if (name == ''):
            return None

        age = self._get_field_value(row, self._age_field)
        birthdate = self._get_field_value(row, self._birthdate_field)
        photo_filename = self._get_field_value(row, self._photo_filename_field)
        photo_source = self._get_field_value(row, self._photo_source_field)
        date_value = self._parse_date(self._get_field_value(row, self._date_field))
        tdor_list_ref = self._get_field_value(row, self._tdor_list_ref_field)
        address = self._get_field_value(row, self._address_field)
        locality = self._get_field_value(row, self._locality_field)
        municipality = self._get_field_value(row, self._municipality_field)
        state = self._get_field_value(row, self._state_field)
        location = self._get_location_from_row(row)
        country = self._get_field_value(row, self._country_field)
        latitude = self._get_field_value(row, self._latitude_field)
        longitude = self._get_field_value(row, self._longitude_field)
        category = self._get_field_value(row, self._category_field)
        cause = self._get_field_value(row, self._cause_field)
        description = self._get_field_value(row, self._description_field)
        post_text = self._get_field_value(row, self._post_text_field)
        permalink = self._get_field_value(row, self._permalink_field)
        uid = self._get_uid_from_permalink(permalink)

        report = Report()

        report.set_name(name)
        report.set_age(age)
        report.set_birthdate(birthdate)
        report.set_photo_filename(photo_filename)
        report.set_photo_source(photo_source)
        report.set_date(date_value)
        report.set_tdor_list_ref(tdor_list_ref)
        report.set_address(address)
        report.set_locality(locality)
        report.set_municipality(municipality)
        report.set_state(state)
        report.set_location(location)
        report.set_country(country)
        report.set_latitude(latitude)
        report.set_longitude(longitude)
        report.set_category(category)
        report.set_cause(cause)
        report.set_description(description)
        report.set_post_text(post_text)
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
        if (self._municipality_field >= 0 and len(row[self._municipality_field]) > 0):
            return f"{row[self._municipality_field]}, {row[self._state_field]}"
        elif self._state_field >= 0:
            return row[self._state_field]

        return row[self._location_field].strip()


    def _get_uid_from_permalink(self, permalink):
        if permalink:
            return permalink.split('_')[-1]

        return None