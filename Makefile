# FOSSology+SPDX (R) Makefile
# Copyright (C) 2013 University of Nebraska at Omaha.

# pull in all our default variables
include Makefile.conf

# the directories we do things in by default
DIRS= utils src

# create lists of targets for various operations
# these are phony targets (declared at bottom) of convenience so we can
# run 'make $(operation)-$(subdir)'. Yet another convenience, a target of
# '$(subdir)' is equivalent to 'build-$(subdir)'
BUILDDIRS = $(DIRS:%=build-%)
INSTALLDIRS = $(DIRS:%=install-%)
UNINSTALLDIRS = $(DIRS:%=uninstall-%)
CLEANDIRS = $(DIRS:%=clean-%)
all: $(BUILDDIRS)
$(DIRS): $(BUILDDIRS)
$(BUILDDIRS):
	$(MAKE) -s -C $(@:build-%=%)

## Targets

# generate the VERSION file
TOP = .
VERSIONFILE: 
	$(call WriteVERSIONFile,"BUILD")

# install depends on everything being built first
install: all $(INSTALLDIRS)
$(INSTALLDIRS):
	$(MAKE) -s -C $(@:install-%=%) install
	@echo "Making configuration directories"
	@if [ ! -e $(DESTDIR)$(SYSCONFDIR) ] ; then \
		mkdir -p $(DESTDIR)$(SYSCONFDIR); \
	fi
	@echo "Making output_file directory"
	@if [ ! -e $(DESTDIR)$(OUTPUT_FILE) ] ; then \
		mkdir -p $(DESTDIR)$(OUTPUT_FILE); \
		/bin/chmod 777 $(DESTDIR)$(OUTPUT_FILE); \
	fi
	@echo "Making log directory"
	@if [ ! -e $(DESTDIR)$(LOGDIR) ] ; then \
		mkdir -p $(DESTDIR)$(LOGDIR); \
		/bin/chmod 777 $(DESTDIR)$(LOGDIR); \
	fi

uninstall: $(UNINSTALLDIRS)
$(UNINSTALLDIRS):
	$(MAKE) -s -C $(@:uninstall-%=%) uninstall
	
clean: $(CLEANDIRS)
$(CLEANDIRS):
	$(MAKE) -s -C $(@:clean-%=%) clean

.PHONY: subdirs $(BUILDDIRS)
.PHONY: subdirs $(DIRS)
.PHONY: subdirs $(UNINSTALLDIRS)
.PHONY: subdirs $(CLEANDIRS)
.PHONY: all install uninstall clean
