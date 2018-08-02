
#C.O.D.E.M.

###Cooperative for Digital Editions at the MHS


##Overview

As part of a grant from the NHPRC and Melon to explore building an online publishing cooperative, the MHS is beginning to refactor its existing TEI document publishing platform software. The goal is for the application to be a complete web-based environment for publishing TEI XML documents of historical scholarly texts. It will feature:

* Backend tool for converting uploading XML and gathering metadata for browse and search tools
* Browse and search on people, subjects, dates, and full text
* Navigation among documents from searches or curated topical groupings
* Intergration with popular open-source CMS platform, which will provide authentication and user management, creation of supporting web pages and features (blogging, description, social media etc.)

It will draw upon at least these other projects: TEI-Oxgarage, Apache SOLR

At least, that's the idea so far. An non-open source version of all this is currently powering many of the Massachusetts Historical Society's online publications, including the (Adams Papers Digital Edition)[http://www.masshist.org/publications/adams-papers], the (Robert Treat Paine Papers)[http://www.masshist.org/publications/rtpp], and the (John Quincy Adams Diary)[http://www.masshist.org/publications/jqadiaries]


##CODEM Status

Early days. Right now, what's in the repository is only the code for converting Oxgarage output to TEI, using a set of markers in MS Word files. Everything is highly coupled with proprietary MHS libraries; these will be refactored and made open-source. Steady progress and commits will be seen when and if we get the implementation grant.


