######################################################################
# Copyright (C) 2013 University of Nebraska at Omaha.
# 
# This program is free software; you can redistribute it and/or
# modify it under the terms of the Apache License, Version 2.0
# as published by the Apache Software Foundation.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# Apache License for more details.
# 
# You should have received a copy of the Apache License along
# with this program; if not, contact to the Apache Software Foundation.
######################################################################

#
# Makefile for spdx
TOP = ../..
VARS = $(TOP)/Makefile.conf
include $(VARS)

DIRS = ui
MOD_NAME=spdx
OUPPUT_FILE_FOLDER_NAME=output_file

#dirloop=for dir in $(DIRS); do $(MAKE) -s -C $$dir $(1);  done

all: 
#VERSIONFILE
#$(call dirloop, )


# generate the VERSION file
VERSIONFILE: 
	#$(call WriteVERSIONFile,"spdx")

install: all
	@echo "make $(MOD_NAME) install"
	#$(call dirloop,install)
	#$(INSTALL_DATA) VERSION $(DESTDIR)$(MODDIR)/$(MOD_NAME)/VERSION
	@if [ -e $(DESTDIR)$(SRCMODDIR) ] ; then \
		mkdir -p $(DESTDIR)$(PREFIX_SRC)$(DATAROOTDIR)/$(MODDIR)/$(MOD_NAME); \
		mkdir $(DESTDIR)$(PREFIX_SRC)$(DATAROOTDIR)/$(UIDIR); \
		rsync -av ./ui/* $(DESTDIR)$(PREFIX_SRC)$(DATAROOTDIR)/$(UIDIR); \
		chown -R fossy.fossy $(DESTDIR)$(PREFIX_SRC)$(DATAROOTDIR)/$(MODDIR)/*; \
		chmod 777 $(DESTDIR)$(PREFIX_SRC)$(DATAROOTDIR)/$(UIDIR)/$(OUPPUT_FILE_FOLDER_NAME); \
		ln -s $(DESTDIR)$(PREFIX_SRC)$(DATAROOTDIR)/$(MODDIR)/$(MOD_NAME) $(DESTDIR)$(INITDIR)/$(PROJECT)/mods-enabled/$(MOD_NAME); \
		ln -s $(DESTDIR)$(PREFIX_SRC)$(DATAROOTDIR)/$(MODDIR)/$(MOD_NAME)/ui/output_module $(DESTDIR)$(PREFIX_SRC)$(DATAROOTDIR)/$(WEBDIR)/ui/spdx-output-module; \
	else \
		mkdir -p $(DESTDIR)$(PREFIX)$(DATAROOTDIR)/$(MODDIR)/$(MOD_NAME); \
		#@mkdir $(DESTDIR)/usr/fossology/spdx/ui; \
		mkdir $(DESTDIR)$(PREFIX)$(DATAROOTDIR)/$(UIDIR); \
		#@rsync -av ./ui/* $(DESTDIR)/usr/fossology/spdx/ui; \
		rsync -av ./ui/* $(DESTDIR)$(PREFIX)$(DATAROOTDIR)/$(UIDIR); \
		chown -R fossy.fossy $(DESTDIR)$(PREFIX)$(DATAROOTDIR)/$(MODDIR)/*; \
		chmod 777 $(DESTDIR)$(PREFIX)$(DATAROOTDIR)/$(UIDIR)/$(OUPPUT_FILE_FOLDER_NAME); \
		#ln -s /usr/share/fossology/spdx /etc/fossology/mods-enabled/spdx; \
		#ln -s /usr/share/fossology/spdx/ui/spdx /usr/share/fossology/www/ui/spdx; \
		ln -s $(DESTDIR)$(PREFIX)$(DATAROOTDIR)/$(MODDIR)/$(MOD_NAME) $(DESTDIR)$(INITDIR)/$(PROJECT)/mods-enabled/$(MOD_NAME); \
		ln -s $(DESTDIR)$(PREFIX)$(DATAROOTDIR)/$(MODDIR)/$(MOD_NAME)/ui/output_module $(DESTDIR)$(PREFIX)$(DATAROOTDIR)/$(WEBDIR)/ui/spdx-output-module; \
	fi

uninstall:
	@echo "make $(MOD_NAME) uninstall"
	#$(call dirloop,uninstall)
	rm -rf $(DESTDIR)$(PREFIX_SRC)$(DATAROOTDIR)/$(MODDIR)/$(MOD_NAME)
	rm -rf $(DESTDIR)$(PREFIX)$(DATAROOTDIR)/$(MODDIR)/$(MOD_NAME)
	rm -f $(DESTDIR)$(INITDIR)/$(PROJECT)/mods-enabled/$(MOD_NAME)
	rm -f $(DESTDIR)$(PREFIX_SRC)$(DATAROOTDIR)/$(WEBDIR)/ui/spdx-output-module
	rm -f $(DESTDIR)$(PREFIX)$(DATAROOTDIR)/$(WEBDIR)/ui/spdx-output-module

clean:
	rm -rf VERSION
	#$(call dirloop,clean)

.PHONY: all install uninstall clean VERSIONFILE
