import os
import subprocess
import sys
import stat

from datetime import datetime
from Report import Report


class ReportsCsvWriter:
    def __init__(self):
        self.data = []


    def get_location_from_location_string(self, location_string):
        opening_bracket_pos = location_string.find('(')

        location_with_state     = location_string[:opening_bracket_pos].strip()

        comma_pos = location_with_state.find( ',')
        if (comma_pos > 0):
            location            = location_with_state[:comma_pos].strip()
        else:
            location            = location_with_state

        return location


    def get_state_from_location_string(self, location_string):
        state = ''

        comma_pos = location_string.find(',')
        if (comma_pos > 0):
            state_with_country  = location_string[comma_pos:].strip()
        
            opening_bracket_pos = state_with_country.find('(')

            if (opening_bracket_pos > 0):
                state               = state_with_country[:opening_bracket_pos].strip()[2:]

        return state


    def get_country_from_location_string(self, location_with_country):
        country = ''

        opening_bracket_pos     = location_with_country.find('(')
        closing_bracket_pos     = location_with_country.find(')')
        comma_pos               = location_with_country.find(',')

        if ( (opening_bracket_pos > 0) and (closing_bracket_pos > 0) ):
            country                 = location_with_country[opening_bracket_pos + 1:closing_bracket_pos].strip()
        else:
            if (comma_pos > 0):
                country                 = location_with_country[comma_pos + 1].strip()

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
        text = 'Name,Age,Birthdate,Photo,Photo source,Date,TDoR list ref,Address,Locality,Town/City/Municipality,State/Province,Country,Latitude,Longitude,Category,Cause of death,Description,Tweet,Permalink'

        return text;


    def get_entry(self, Report, tdor_list_ref_prefix = "tgeu"):
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
        tdor_list_ref       = tdor_list_ref_prefix + '/' + tgeu_date_str + '/' + Report.get_name()
        address             = ''
        locality            = ''
        
        location            = Report.get_location()

        municipality        = self.get_location_from_location_string(location)
        state               = self.get_state_from_location_string(location)
        country             = self.get_country_from_location_string(location)
        latitude            = ''
        longitude           = ''
        category            = self.get_category_from_cause(Report.get_cause() )
        reported_by         = Report.get_reported_by()

        description         = Report.get_remarks() + '\n\n'

        if reported_by and (reported_by != 'unknown'):
            description     += reported_by + '\n\n'

        description         += Report.get_source()

        tweet               = ''

        text = (self.quote_if_necessary(Report.get_name() ) + delimiter +
                self.quote_if_necessary(Report.get_age() ) + delimiter +
                self.quote_if_necessary(birthdate) + delimiter +
                self.quote_if_necessary(photo_filename) + delimiter +
                self.quote_if_necessary(photo_source) + delimiter +
                self.quote_if_necessary(date_str) + delimiter +
                self.quote_if_necessary(tdor_list_ref) + delimiter +
                self.quote_if_necessary(address) + delimiter +
                self.quote_if_necessary(locality) + delimiter +
                self.quote_if_necessary(municipality) + delimiter +
                self.quote_if_necessary(state) + delimiter +
                self.quote_if_necessary(country) + delimiter +
                self.quote_if_necessary(latitude) + delimiter +
                self.quote_if_necessary(longitude) + delimiter +
                self.quote_if_necessary(category) + delimiter +
                self.quote_if_necessary(Report.get_cause() ) + delimiter +
                self.quote_if_necessary(description) + delimiter +
                self.quote_if_necessary(tweet) + delimiter)

        return text;


    def write_file(self, reports, tdor_list_ref_prefix, pathname):
        f = open(pathname, 'w', encoding="utf-8") 

        f.write(u'\ufeff')
        f.write(self.get_header() + '\n') 

        for report in reports:
            f.write(self.get_entry(report, tdor_list_ref_prefix) + '\n')            

        f.close() 
