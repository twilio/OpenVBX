# We expect one script per line, and the script should end in .css (not .min.css)
MIN_STYLES := $(patsubst %.css,%.min.css,$(shell cat site-css.sources))

LITERAL_STYLES = 
STYLES = $(LITERAL_STYLES) $(MIN_STYLES)
REVNUM = 1010
TARGET = site
TMPDIR := $(shell mktemp -d /tmp/openvbx.XXXXXXXXXXXXXXXXXXXXXX)

%.min.css: %.css
	./minify $< > $(TMPDIR)/$(notdir $@)

all: Makefile $(STYLES)
	cat $(addprefix $(TMPDIR)/, $(notdir $(STYLES))) > $(TARGET)-$(REVNUM).css
	rm -rf $(TMPDIR)

clean:
	rm -f $(TARGET)-$(REVNUM).css

development:
	ln -fs master.css $(TARGET)-$(REVNUM).css


