from datetime import datetime


def parse_date(date_str, month_hint = 0):
    
    date_strs = []
    date_strs.append(date_str)                                                              # 1st entry is the original format

    # Bodge to nuke unnecessary noise.
    date_str = date_str.replace('nd', '')
    date_str = date_str.replace('rd', '')
    date_str = date_str.replace('st', '')
    date_str = date_str.replace('th', '')
    date_str = date_str.replace('Augu', 'August')

    delimiter = '/'

    if (date_str.find('/') == -1):                                                          # First try numeric date formats
        delimiter = '.'

    try:
        d1 = datetime.strptime(date_str, '%d' + delimiter + '%m'  + delimiter + '%Y')           # 20/11/2008 or 20.11.2008
    except ValueError:
        try:
            d1 = datetime.strptime(date_str, '%d' + delimiter + '%m'  + delimiter + '%y')           # 20/11/08 or 20.11.08
        except ValueError:
            d1 = None

    if ( (d1 != None) and ( (month_hint == 0) or (d1.month == month_hint) ) ):
        date_strs.append(d1.strftime('%Y-%m-%d') )

    try:
        d2 = datetime.strptime(date_str, '%m' + delimiter + '%d' + delimiter + '%Y')            # 11/20/2008 or 11.20.2008
    except ValueError:
        try:
            d2 = datetime.strptime(date_str, '%m' + delimiter + '%d' + delimiter + '%y')            # 11/20/08 or 11.20.08
        except ValueError:
            d2 = None


    if ( (d2 != None) and ( (month_hint == 0) or (d2.month == month_hint) ) ):
        if (d1 != d2):
            date_strs.append(d2.strftime('%Y-%m-%d') )

    if (len(date_strs) == 1):                                                               # Now try alphanumeric date formats
        try:
            d = datetime.strptime(date_str, '%d %b %Y')                                         # 20 Nov 2008
        except ValueError:
            try:
                d = datetime.strptime(date_str, '%b %d %Y')                                     # Nov 20 2008
            except ValueError:
                try:
                    d = datetime.strptime(date_str, '%B %d %Y')                                 # November 20 2008
                except ValueError:
                    try:
                        d = datetime.strptime(date_str, '%d %B %Y')                             # 20 November 2008
                    except ValueError:
                        d = None

        if (d != None):
            date_strs.append(d.strftime('%Y-%m-%d') )

    return date_strs


