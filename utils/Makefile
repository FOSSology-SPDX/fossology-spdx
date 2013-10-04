# FOSSology+SPDX Makefile - utils/
# Copyright (C) 2013 University of Nebraska at Omaha.

# Create and install:
#  fossyspdx-postinstall


TOP=..
VARS=$(TOP)/Makefile.conf
include $(VARS)

CONFPATH=$(SYSCONFDIR)

all:	fossyspdx-postinstall defconf/fossologyspdx.conf

# include the preprocessing stuff
include $(TOP)/Makefile.process

# generate the postinstall script
fossyspdx-postinstall:	fossyspdx-postinstall-process
	chmod +x fossyspdx-postinstall

# generate fossologyspdx.conf
defconf/fossologyspdx.conf:	defconf/fossologyspdx.conf-process

install: all
	$(INSTALL_PROGRAM) fossyspdx-postinstall $(DESTDIR)$(LIBEXECDIR)/fossyspdx-postinstall
	$(INSTALL_PROGRAM) fossyspdxinit.php $(DESTDIR)$(LIBEXECDIR)/fossyspdxinit.php
	echo "Making configuration directories"; \
	if [ ! -e $(DESTDIR)$(CONFPATH) ] ; then \
		mkdir -p $(DESTDIR)$(CONFPATH); \
	fi
	if [ ! -e $(DESTDIR)$(CONFPATH)/conf ] ; then \
		mkdir -p $(DESTDIR)$(CONFPATH)/conf; \
	fi
	echo "Installing configuration files..."
	
	if [ ! -f $(DESTDIR)$(CONFPATH)/fossologyspdx.conf -o "$(OVERWRITE)" ] ; then \
		echo "NOTE: using default version for $(DESTDIR)$(CONFPATH)/fossologyspdx.conf"; \
	 	$(INSTALL) -m 666 defconf/fossologyspdx.conf $(DESTDIR)$(CONFPATH)/fossologyspdx.conf; \
	else \
		echo "WARNING: $(DESTDIR)$(CONFPATH)/fossologyspdx.conf already exists."; \
		echo "  Not overwriting, consider checking it by hand or use the OVERWRITE option."; \
	fi

uninstall:
	rm -f $(DESTDIR)$(LIBEXECDIR)/fossyspdx-postinstall
	rm -f $(DESTDIR)$(LIBEXECDIR)/fossyspdxinit.php

	@if [ -d $(DESTDIR)$(LIBEXECDIR) ]; then \
		if [ "`ls -A $(DESTDIR)$(LIBEXECDIR)`" ]; then \
			echo "WARNING: $(DESTDIR)$(LIBEXECDIR) not empty, not removing";\
		else \
			rmdir $(DESTDIR)$(LIBEXECDIR); \
		fi; \
	fi

	@echo "Configuration files will not be removed:"
	@echo "      $(DESTDIR)$(CONFPATH)/fossologyspdx.conf"
	@echo "Remove by hand if you desire."

clean:
	rm -f fossyspdx-postinstall defconf/fossologyspdx.conf

test: all
	@echo "NOTICE: no tests available for install"

coverage:
        @echo "NOTICE: no coverage available for install"

.PHONY: all install uninstall clean test coverage
