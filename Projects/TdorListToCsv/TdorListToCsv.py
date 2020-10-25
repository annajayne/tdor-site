import os
import subprocess
import sys
import stat
import glob
import unittest



from pathlib import Path
from ParseDateStr import parse_date
from Report import Report
from ReadTgeuTextFile import TgeuTextFileReader
from ReportsCsvWriter import ReportsCsvWriter



class Test_date(unittest.TestCase):
    def test_parse_date_month_abbrevs_day_first(self):
        d = parse_date('20 Nov 2008')
        self.assertEqual(2, len(d) )

        self.assertEqual('20 Nov 2008', d[0])
        self.assertEqual('2008-11-20', d[1])


    def test_parse_date_month_abbrevs_month_first(self):
        d = parse_date('Nov 20 2008')
        self.assertEqual(2, len(d) )

        self.assertEqual('Nov 20 2008', d[0])
        self.assertEqual('2008-11-20', d[1])

    def test_parse_date_month_name_day_first(self):
        d = parse_date('20th November 2008')
        self.assertEqual(2, len(d) )

        self.assertEqual('20th November 2008', d[0])
        self.assertEqual('2008-11-20', d[1])


    def test_parse_date_month_name_month_first(self):
        d = parse_date('November 22nd 2008')
        self.assertEqual(2, len(d) )

        self.assertEqual('November 22nd 2008', d[0])
        self.assertEqual('2008-11-22', d[1])


    def test_parse_ambiguous_date_slashes(self):
        d = parse_date('11/6/2008')
        self.assertEqual(3, len(d) )

        self.assertEqual('11/6/2008', d[0])
        self.assertEqual('2008-06-11', d[1])
        self.assertEqual('2008-11-06', d[2])


    def test_parse_ambiguous_date_slashes_short_year(self):
        d = parse_date('6/9/12')
        self.assertEqual(3, len(d) )

        self.assertEqual('6/9/12', d[0])
        self.assertEqual('2012-09-06', d[1])
        self.assertEqual('2012-06-09', d[2])


    def test_parse_ambiguous_date_dots(self):
        d = parse_date('11.6.2008')
        self.assertEqual(3, len(d) )

        self.assertEqual('11.6.2008', d[0])
        self.assertEqual('2008-06-11', d[1])
        self.assertEqual('2008-11-06', d[2])


    def test_parse_ambiguous_date_dots_with_hint(self):
        d = parse_date('11.6.2008', 6)
        self.assertEqual(2, len(d) )

        self.assertEqual('11.6.2008', d[0])
        self.assertEqual('2008-06-11', d[1])



class Test_read_tdor_2009(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/TvT_TMM_TDOR2009_Namelist_EN.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);

       
    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 162)



class Test_read_tdor_2010(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/TvT_TMM_TDOR2010_Namelist_EN.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);

       
    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 168)           # The summary text at the start of the list states 179, but only 168 are listed - and one of those is listed twice (so 167 really)



class Test_read_tdor_2011(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/TvT_TMM_TDOR2011_Namelist_EN.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);

       
    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 221)



class Test_read_tdor_2012(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/TvT-TMM-TDOR2012-Namelist-en.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);

       
    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 265)



class Test_read_tdor_2013(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/TvT-TMM-Namelist-TDOR2013_EN.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);

       
    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 238)



class Test_read_tdor_2014(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/TvT_TMM_TDOR2014_Namelist_EN.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);

       
    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 226)



class Test_read_tdor_2015(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/EN_TvT-TMM-Namelist-TDOR-2015-Oct-2014-Sep-2015.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);

       
    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 271)



class Test_read_tdor_2016(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/TvT_TMM_TDoR2016_Namelist_EN.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);


    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 295)


    def test_first_record(self):
        # Name:                                 Alvari  
        # Age:                                  not reported  
        # Date of death:                        10/01/2015  
        # Location of death:                    Vitoria (Brazil)  
        # Cause of death:                       not reported  
        # Remarks:                              Alvari was found in advaced decomposing state without any body perforation.  
        # Source:                               TvT partner organisation Grupo Gay da Bahia & Manchete Digital 01.10.2015   

        report = self.reports[0]

        self.assertEqual(report.get_name(),     'Alvari')
        self.assertEqual(report.get_age(),      '')
        self.assertEqual(report.get_date(),     '2015-10-01')
        self.assertEqual(report.get_location(), 'Vitoria (Brazil)')
        self.assertEqual(report.get_cause(),    'not reported')
        self.assertEqual(report.get_remarks(),  'Alvari was found in advaced decomposing state without any body perforation.')
        self.assertEqual(report.get_source(),   'TvT partner organisation Grupo Gay da Bahia & Manchete Digital 01.10.2015')


    def test_multiline_record(self):
        # Name:                                 Rafael da Silva Machado  
        # Age:                                  17  
        # Date of death:                        27/09/2016  
        # Location of death:                    Porto Alegre (Brazil)  
        # Cause of death:                       shot  
        # Remarks:                              According to preliminary information from the Military Brigade, a car crossed a street and  
        #                                       shots came. Witnesses heard at least 15 shots and said the car still ran over and dragged  
        #                                       the victim about half a block to the location where the body was found.  
        # Source:                               TvT partner organisation Rede Trans Brasil & Correio do Povo, 27.09.2016   

        report = self.reports[289]

        self.assertEqual(report.get_name(),     'Rafael da Silva Machado')
        self.assertEqual(report.get_age(),      '17')
        self.assertEqual(report.get_date(),     '2016-09-27')
        self.assertEqual(report.get_location(), 'Porto Alegre (Brazil)')
        self.assertEqual(report.get_cause(),    'shot')
        self.assertEqual(report.get_remarks(),  'According to preliminary information from the Military Brigade, a car crossed a street and shots came. Witnesses heard at least 15 shots and said the car still ran over and dragged the victim about half a block to the location where the body was found.')
        self.assertEqual(report.get_source(),   'TvT partner organisation Rede Trans Brasil & Correio do Povo, 27.09.2016')


    def test_last_record(self):
        # Name:                                 N.N.  
        # Age:                                  not reported  
        # Date of death:                        not reported  
        # Location of death:                    Baja California (Mexico)  
        # Cause of death:                       not reported  
        # Remarks:                              The body had the legs naked and the ankles fastened with black tape. The victim was  
        #                                       found by the police covered with a blanket.  
        # Source:                               TvT partner organisation Centro de Apoyo a las Identidades Trans & Jornada BC,  
        #                                       29.09.2016   

        report = self.reports[294]

        self.assertEqual(report.get_name(),     'Name Unknown')
        self.assertEqual(report.get_age(),      '')
        self.assertEqual(report.get_date(),     'not reported')
        self.assertEqual(report.get_location(), 'Baja California (Mexico)')
        self.assertEqual(report.get_cause(),    'not reported')
        self.assertEqual(report.get_remarks(),  'The body had the legs naked and the ankles fastened with black tape. The victim was found by the police covered with a blanket.')
        self.assertEqual(report.get_source(),   'TvT partner organisation Centro de Apoyo a las Identidades Trans & Jornada BC, 29.09.2016')



class Test_read_tdor_2017(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/TvT_TMM_TDoR2017_Namelist_EN.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);


    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 326)



class Test_read_tdor_2018(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/TvT_TMM_TDoR2018_Namelist_EN.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);


    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 368)



class Test_read_tdor_2019(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/TvT_TMM_TDoR2019_Namelist_EN.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);


    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 331)



class Test_read_tdor_2020(unittest.TestCase):
    def setUp(self):
        folder = os.path.dirname(os.path.realpath(__file__) )

        self.txt_file_pathname  = folder + "/data/TvT_TMM_TDoR2020_Namelist_EN.txt"

        reader = TgeuTextFileReader()
        self.reports = reader.read(self.txt_file_pathname);


    def test_count(self):
        count = len(self.reports)

        self.assertEqual(count, 0)



class Test_ReportsCsvWriter(unittest.TestCase):
    def setUp(self):
        self.reports = []

        report = Report()

        report.set_name('Alvari')
        report.set_age('not reported')
        report.set_date('10/01/2015')
        report.set_location('Vitoria (Brazil)')
        report.set_cause('not reported')
        report.set_remarks('Alvari was found in advaced decomposing state without any body perforation.')
        report.set_source('TvT partner organisation Grupo Gay da Bahia & Manchete Digital 01.10.2015')

        self.reports.append(report)


    def test_get_csv_header(self):
        writer          = ReportsCsvWriter();

        self.assertEqual(writer.get_header(),           'Name,Age,Photo,Photo source,Date,Source ref,Location,State/Province,Country,Latitude,Longitude,Cause of death,Description,Tweet,Permalink')


    def test_get_csv_entry(self):
        writer          = ReportsCsvWriter();

        report = self.reports[0]

        expected_entry  = "Alvari,not reported,,,10/01/2015,tgeu/10/01/2015/Alvari,Vitoria,,Brazil,,,not reported,\"Alvari was found in advaced decomposing state without any body perforation.\n\nTvT partner organisation Grupo Gay da Bahia & Manchete Digital 01.10.2015\",,"

        actual_entry    = writer.get_entry(report)

        if (actual_entry != expected_entry):
            # Text is long, so print in full to aid diagnosing test failures

            print('\r\n\r\nExpected:\r\n')
            print(expected_entry)

            print('\r\n\r\nActual:\r\n')
            print(actual_entry)

        self.assertEqual(writer.get_entry(report),      expected_entry)


    def test_write_csv_file(self):
        writer          = ReportsCsvWriter();

        pathname        = 'ReportsCsvWriterTest.csv'

        if (os.path.isfile(pathname) ):
            os.remove(pathname)

        writer.write_file(self.reports, pathname)

        self.assertTrue(os.path.isfile(pathname) )

        f = open(pathname, encoding="utf-8")

        file_contents = f.read()
        f.close()

        self.assertTrue(len(file_contents) > 0)



def write_csv_file(txt_file_pathname, csv_file_pathname):
    if (os.path.isfile(csv_file_pathname) ):
        os.remove(csv_file_pathname)

    reader              = TgeuTextFileReader()
    writer              = ReportsCsvWriter()

    print('Reading ' + txt_file_pathname)
    reports             = reader.read(txt_file_pathname)

    print('Writing ' + csv_file_pathname + '\n')
    return writer.write_file(reports, csv_file_pathname)



if __name__ == '__main__':
    unittest.main(exit=False)


folder = os.path.dirname(os.path.realpath(__file__) ) + '/data'

txt_file_pathnames = glob.glob(folder + '/*.txt')

for txt_file_pathname in txt_file_pathnames:
    path                = Path(txt_file_pathname)
    csv_file_pathname   = path.with_suffix('').as_posix() + '.csv'

    write_csv_file(txt_file_pathname, csv_file_pathname)

