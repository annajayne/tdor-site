import os
import sys
import unittest

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '../PyTdorLib'))

from Report import Report
from TgeuTextFileReader import TgeuTextFileReader


class Test_TgeuTextFileReader(unittest.TestCase):
    def setUp(self):
        self.reader = TgeuTextFileReader()

        
    def _get_test_file_path(self, filename):
        # These tests use the data folder from the TdorsListToCsv project, which contains the test files
        data_path = os.path.join(os.path.dirname(__file__), "../TdorListToCsv/data")

        return os.path.join(data_path, filename)


    def _read_reports(self, filename):
        input_path = self._get_test_file_path(filename)

        return self.reader.read(input_path, "tgeu")


    def test_tdor2008_count(self):
        reports = self._read_reports("TvT_TMM_Feb2010_Namelist2008-2009_EN.txt")

        count = len(reports)

        self.assertEqual(count, 333)


    def test_tdor2009_count(self):
        reports = self._read_reports("TvT_TMM_TDOR2009_Namelist_EN.txt")

        count = len(reports)

        self.assertEqual(count, 162)


    def test_tdor2010_count(self):
        reports = self._read_reports("TvT_TMM_TDOR2010_Namelist_EN.txt")

        count = len(reports)

        self.assertEqual(count, 168)            # The summary text at the start of the list states 179, but only 168 are listed - and one of those is listed twice (so 167 really)


    def test_tdor2011_count(self):
        reports = self._read_reports("TvT_TMM_TDOR2011_Namelist_EN.txt")

        count = len(reports)

        self.assertEqual(count, 221)


    def test_tdor2012_count(self):
        reports = self._read_reports("TvT-TMM-TDOR2012-Namelist-en.txt")

        count = len(reports)

        self.assertEqual(count, 265)


    def test_tdor2013_count(self):
        reports = self._read_reports("TvT-TMM-Namelist-TDOR2013_EN.txt")

        count = len(reports)

        self.assertEqual(count, 238)


    def test_tdor2014_count(self):
        reports = self._read_reports("TvT_TMM_TDOR2014_Namelist_EN.txt")

        count = len(reports)

        self.assertEqual(count, 226)


    def test_tdor2015_count(self):
        reports = self._read_reports("EN_TvT-TMM-Namelist-TDOR-2015-Oct-2014-Sep-2015.txt")

        count = len(reports)

        self.assertEqual(count, 271)


    def test_tdor2016_count(self):
        reports = self._read_reports("TvT_TMM_TDoR2016_Namelist_EN.txt")

        count = len(reports)

        self.assertEqual(count, 295)


    def test_tdor2016_first_report(self):
        # Name:                                         Alvari  
        # Age:                                          not reported  
        # Date of death:                                10/01/2015  
        # Location of death:                            Vitoria (Brazil)  
        # Cause of death:                               not reported  
        # Remarks:                                      Alvari was found in advaced decomposing state without any body perforation.  
        # Source:                                       TvT partner organisation Grupo Gay da Bahia & Manchete Digital 01.10.2015   

        reports = self._read_reports("TvT_TMM_TDoR2016_Namelist_EN.txt")

        report = reports[0]

        self.assertEqual(report.get_name(),             'Alvari')
        self.assertEqual(report.get_age(),              '')
        self.assertEqual(report.get_date(),             '2015-10-01')
        self.assertEqual(report.get_tdor_list_ref(),    'tgeu/2015-10-01/Alvari')
        self.assertEqual(report.get_location(),         'Vitoria (Brazil)')
        self.assertEqual(report.get_cause(),            'not reported')
        self.assertEqual(report.get_remarks(),          'Alvari was found in advaced decomposing state without any body perforation.')
        self.assertEqual(report.get_source(),           'TvT partner organisation Grupo Gay da Bahia & Manchete Digital 01.10.2015')

        # The description field is generated from the remarks, reported by and source fields
        self.assertEqual(report.get_description(),      report.get_remarks() + '\n\n' + report.get_source())


    def test_tdor2016_multiline_report(self):
        # Name:                                         Rafael da Silva Machado  
        # Age:                                          17  
        # Date of death:                                27/09/2016  
        # Location of death:                            Porto Alegre (Brazil)  
        # Cause of death:                               shot  
        # Remarks:                                      According to preliminary information from the Military Brigade, a car crossed a street and  
        #                                               shots came. Witnesses heard at least 15 shots and said the car still ran over and dragged  
        #                                               the victim about half a block to the location where the body was found.  
        # Source:                                       TvT partner organisation Rede Trans Brasil & Correio do Povo, 27.09.2016   

        reports = self._read_reports("TvT_TMM_TDoR2016_Namelist_EN.txt")

        report = reports[289]

        self.assertEqual(report.get_name(),             'Rafael da Silva Machado')
        self.assertEqual(report.get_age(),              '17')
        self.assertEqual(report.get_date(),             '2016-09-27')
        self.assertEqual(report.get_tdor_list_ref(),    'tgeu/2016-09-27/Rafael da Silva Machado')
        self.assertEqual(report.get_location(),         'Porto Alegre (Brazil)')
        self.assertEqual(report.get_cause(),            'shot')
        self.assertEqual(report.get_remarks(),          'According to preliminary information from the Military Brigade, a car crossed a street and shots came. Witnesses heard at least 15 shots and said the car still ran over and dragged the victim about half a block to the location where the body was found.')
        self.assertEqual(report.get_source(),           'TvT partner organisation Rede Trans Brasil & Correio do Povo, 27.09.2016')

        # The description field is generated from the remarks, reported by and source fields
        self.assertEqual(report.get_description(),      report.get_remarks() + '\n\n' + report.get_source())


    def test_tdor2016_last_report(self):
        # Name:                                         N.N.  
        # Age:                                          not reported  
        # Date of death:                                not reported  
        # Location of death:                            Baja California (Mexico)  
        # Cause of death:                               not reported  
        # Remarks:                                      The body had the legs naked and the ankles fastened with black tape. The victim was  
        #                                               found by the police covered with a blanket.  
        # Source:                                       TvT partner organisation Centro de Apoyo a las Identidades Trans & Jornada BC,  
        #                                               29.09.2016   

        reports = self._read_reports("TvT_TMM_TDoR2016_Namelist_EN.txt")

        report = reports[294]

        self.assertEqual(report.get_name(),             'Name Unknown')
        self.assertEqual(report.get_age(),              '')
        self.assertEqual(report.get_date(),             'not reported')
        self.assertEqual(report.get_tdor_list_ref(),    'tgeu/not reported/Name Unknown')
        self.assertEqual(report.get_location(),         'Baja California (Mexico)')
        self.assertEqual(report.get_cause(),            'not reported')
        self.assertEqual(report.get_remarks(),          'The body had the legs naked and the ankles fastened with black tape. The victim was found by the police covered with a blanket.')
        self.assertEqual(report.get_source(),           'TvT partner organisation Centro de Apoyo a las Identidades Trans & Jornada BC, 29.09.2016')

        # The description field is generated from the remarks, reported by and source fields
        self.assertEqual(report.get_description(),      report.get_remarks() + '\n\n' + report.get_source())


    def test_tdor2017_count(self):
        reports = self._read_reports("TvT_TMM_TDoR2017_Namelist_EN.txt")

        count = len(reports)

        self.assertEqual(count, 326)


    def test_tdor2018_count(self):
        reports = self._read_reports("TvT_TMM_TDoR2018_Namelist_EN.txt")

        count = len(reports)

        self.assertEqual(count, 368)


    def test_tdor2019_count(self):
        reports = self._read_reports("TvT_TMM_TDoR2019_Namelist_EN.txt")

        count = len(reports)

        self.assertEqual(count, 331)


    def test_tdor2020_count(self):
        reports = self._read_reports("TvT_TMM_TDoR2020_Namelist_EN.txt")

        count = len(reports)

        self.assertEqual(count, 350)
