# C.O.D.E.M.

### Cooperative for Digital Editions at the MHS


## Schema Generation, oXygen

1. Open the ODD file in oXygen
1. Choose “Configure Transformation Scenarios…” (button: a wrench w/ a small red triangle; menu: Document > Transformations > Configure Transformation Scenarios…; keyboard: &#x2318;-shift-t)
1. Check the desired transforms (in this case: TEI ODD XHTML, TEI ODD
RELAX NG (XML syntax), TEI ODD RELAX NG (compact syntax))
  - If they are not visible, either clear the filter box (top text box), or use the gear in the upper R of the dialog box to “Show all scenarios” 
1. IIRC, by default this will generate
   - `codem.html`, and probably open it in your default browser
   - `out/codem.rnc`, and probably open it in oXygen
   - `out/codem.rng`, and probably open it in oXygen
1. Move the files in `out/` out of `out/` and into the main `odd/` directory where they belong. (Someday TEI-C will fix this so oXygen puts these directly into the directory where the ODD file is, not into an `out/` subdirectory; but that's likely to take many months.)
1. You have not generated codem.isosch yet. oXygen does not do this on its own.

## Schema Generation, Syd’s commandline

1. `$ cd /path/to/codem/manage/odd/`
1. `$ fiumicino.bash codem`
1. `$ mv codem.doc.html codem.html`
1. edit `codem.html` by hand, PRN — typically this will mean inserting a blank between title &amp; author, or just deleting author(s).
