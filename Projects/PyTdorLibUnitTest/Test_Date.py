import os
import sys
import unittest

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '../PyTdorLib'))

from Report import Report
from ParseDateStr import parse_date


class Test_Date(unittest.TestCase):
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
