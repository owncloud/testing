SHELL := /bin/bash

# directories
build_dir=build
dist_dir=$(build_dir)/dist
app_name=$(notdir $(CURDIR))
package_dir=$(CURDIR)/$(dist_dir)/$(app_name)

# start with displaying help
.DEFAULT_GOAL := help

help:
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'



##
## Build
##--------------------------------------


.PHONY: dist
dist:                      ## create releasable tarball
dist:
	rm -rf $(dist_dir)
	mkdir -p $(dist_dir)
	tar -czf $(dist_dir)/$(app_name).tar.gz  \
	--exclude-vcs \
	--exclude="../$(app_name)/build" \
	../$(app_name)

