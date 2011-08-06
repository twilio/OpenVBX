# We expect one script per line, and the script should end in .js (not .min.js)
MIN_SCRIPTS := $(shell cat site-bootstrap.sources)
EXTERN_SCRIPTS := $(shell cat site-externals.sources)
APP_SCRIPTS := $(patsubst %.js,%.min.js,$(shell cat site-app.sources))
LITERAL_SCRIPTS = 
SCRIPTS = $(LITERAL_SCRIPTS) $(MIN_SCRIPTS)
REVNUM = 1010
COMPILE_DIR = compiled/$(REVNUM)
TARGET = site
TMPDIR := $(shell mktemp -d /tmp/openvbx.XXXXXXXXXXXXXXXXXXXXXX)
empty = 
space = $(empty) $(empty)

%.min.js: %.js
	java -jar compiler.jar --compilation_level SIMPLE_OPTIMIZATIONS \
		 --js $< \
		 > $(TMPDIR)/$(notdir $<)

all: $(APP_SCRIPTS)
	java -jar compiler.jar --compilation_level SIMPLE_OPTIMIZATIONS \
		 --js $(subst $(space),$(space)--js$(space),$(MIN_SCRIPTS)) \
		 --externs $(subst $(space),$(space)--externs$(space),$(EXTERN_SCRIPTS)) \
		 > $(TMPDIR)/$(notdir $(TARGET).js)
	mkdir -p $(COMPILE_DIR)
	cp $(TMPDIR)/*.js $(COMPILE_DIR)/
	rm -rf $(TMPDIR)

clean:
	rm -rf $(COMPILE_DIR)
