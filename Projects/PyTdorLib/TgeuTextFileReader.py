import os
import subprocess
import sys
import stat

from datetime import datetime
from ParseDateStr import parse_date
from Report import Report


class TgeuTextFileReader:
    _tdor_list_ref_prefix = ""


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


    def read(self, pathname, tdor_list_ref_prefix = ""):
        self._tdor_list_ref_prefix = tdor_list_ref_prefix

        f = open(pathname, encoding="utf-8")

        reports = []

        last_property = '';

        report = None

        for line in f:

            if (line[:1] == '#'):
                space_pos = line.find(' ', 2)
                if (space_pos > 0):
                    line = 'Name:' + line[space_pos:]             # Handles the case where the name is something like "# 1 Juliana Ferreira  "

            tokens = line.split(":")

            if (len(tokens) >= 2):
                #print('{} = {}'.format(tokens[0], tokens[1]) )

                property = tokens[0].lower()
                value = tokens[1]

                if (len(tokens) >= 3):
                    value = value + ':' + tokens[2]

                value = value.strip()

                last_property = property;

                if (property == 'name'):
                    if (value == 'N.N.'):
                        value = 'Name Unknown'

                    report = None

                    report = Report()
                    reports.append(report)
                    report.set_name(value)

                elif (property == 'age'):
                    if (value != 'not reported'):
                        report.set_age(value)
                elif (property == 'date of death'):
                    date_value = self.parse_date(value)
                    tdor_list_ref = self._build_tdor_list_ref(date_value, report.get_name())

                    report.set_date(date_value)
                    report.set_tdor_list_ref(tdor_list_ref)
                elif (property == 'location of death'):
                    report.set_location(value)
                elif (property == 'cause of death'):
                    report.set_cause(value)
                elif (property == 'remarks'):
                    # TODO(PyTdorLib): Merge remarks & source ogether into description field. Use local vars as placeholders until the values can be merged at the end of the loop
                    report.set_remarks(value)
                elif ( (property == 'source') or (property == 'sources') ):
                    report.set_source(value)
            elif (last_property == 'remarks'):
                if (len(tokens) == 1):
                    value = tokens[0]
  
                    suffix = value.strip()
                    if ( (len(suffix) > 0) and (report != None) ):
                        remarks = report.get_remarks() + ' ' + suffix

                        # TODO(PyTdorLib): Merge remarks & source ogether into description field. Use local vars as placeholders until the values can be merged at the end of the loop
                        report.set_remarks(remarks)

            elif ( ( (last_property == 'source') or (last_property == 'sources') ) and (report != None) ):
                if (len(tokens) == 1):
                    value = tokens[0]

                    suffix = value.strip()
                    if ( (len(suffix) > 0) and (report != None) ):
                        source = report.get_source() + ' ' + suffix

                        # TODO(PyTdorLib): Consider merging remarks & source together into description field. Use local vars as placeholders until the values can be merged at the end of the loop
                        report.set_source(source)


        f.close()

        return reports


    # TODO(PyTdorLib): This code is shared with TgeuCsvFileReader
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
