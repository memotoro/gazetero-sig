===================================================
 Plugin to export query results to various formats
===================================================

- adds export radio buttons to query result display
- currently omplemented: XLS, PDF, CSV

- Export to XLS requires the installation of the PEAR modules
  Spreadsheet_Excel_Writer and OLE

   => Run the following PEAR comands to install them:
      pear install -f OLE
      pear install -f Spreadsheet_Excel_Writer