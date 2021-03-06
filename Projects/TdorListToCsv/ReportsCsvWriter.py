import os
import subprocess
import sys
import stat

from datetime import datetime
from Report import Report


class ReportsCsvWriter:
    def __init__(self):
        self.data = []


    def get_location_from_location_string(self, location_with_country):
        opening_bracket_pos = location_with_country.find( '(')

        location            = location_with_country[:opening_bracket_pos].strip()

        return location


    def get_state_from_location_string(self, location_with_country):
        return ''


    def get_country_from_location_string(self, location_with_country):
        opening_bracket_pos = location_with_country.find( '(')
        closing_bracket_pos = location_with_country.find( ')')

        country             = location_with_country[opening_bracket_pos + 1:closing_bracket_pos].strip()

        return country


    def get_category_from_cause(self, cause):
        if cause == 'not reported':
            return 'uncategorised'

        return 'violence'


    def quote_if_necessary(self, text):
        adjusted_text = text.replace('"', '""')
        
        if ( (text.find(',') >= 0) or (text.find('\n') >= 0) ):
            adjusted_text = '"' + adjusted_text + '"'

        return adjusted_text


    def get_header(self):
        text = 'Name,Age,Birthdate,Photo,Photo source,Date,Source ref,Location,State/Province,Country,Latitude,Longitude,Category,Cause of death,Description,Tweet,Permalink'

        return text;


    def get_entry(self, Report):
        delimiter           = ','

        photo_filename      = ''
        photo_source        = ''

        date_str            = Report.get_date()
        tgeu_date_str       = date_str

        try:
            dt = datetime.strptime(date_str, '%Y-%m-%d')        # 2017-11-20
            tgeu_date_str   = dt.strftime('%d-%b-%Y')           # 20-Nov-2017
        except ValueError:
            dt = None

        birthdate           = ''
        source_ref          = 'tgeu/' + tgeu_date_str + '/' + Report.get_name()
        location            = self.get_location_from_location_string(Report.get_location() )
        state               = self.get_state_from_location_string(Report.get_location() )
        country             = self.get_country_from_location_string(Report.get_location() )
        latitude            = ''
        longitude           = ''
        category            = self.get_category_from_cause(Report.get_cause() )
        description         = Report.get_remarks() + '\n\n' + Report.get_source()
        tweet               = ''

        text = (self.quote_if_necessary(Report.get_name() ) + delimiter +
                self.quote_if_necessary(Report.get_age() ) + delimiter +
                self.quote_if_necessary(birthdate) + delimiter +
                self.quote_if_necessary(photo_filename) + delimiter +
                self.quote_if_necessary(photo_source) + delimiter +
                self.quote_if_necessary(date_str) + delimiter +
                self.quote_if_necessary(source_ref) + delimiter +
                self.quote_if_necessary(location) + delimiter +
                self.quote_if_necessary(state) + delimiter +
                self.quote_if_necessary(country) + delimiter +
                self.quote_if_necessary(latitude) + delimiter +
                self.quote_if_necessary(longitude) + delimiter +
                self.quote_if_necessary(category) + delimiter +
                self.quote_if_necessary(Report.get_cause() ) + delimiter +
                self.quote_if_necessary(description) + delimiter +
                self.quote_if_necessary(tweet) + delimiter)

        return text;


    def write_file(self, reports, pathname):
        f = open(pathname, 'w', encoding="utf-8") 

        f.write(u'\ufeff')
        f.write(self.get_header() + '\n') 

        for report in reports:
            f.write(self.get_entry(report) + '\n')            

        f.close() 

