
STATICSRC=$(wildcard static/*.html)
STATICDST=$(subst static/, www/, $(STATICSRC))

KNOWNMANUALSRC=$(patsubst doc/%, doc/build/%/split/index.html, $(wildcard doc/??))

ALLMANUALSRC = $(wildcard doc/build/*/split/*.html)
MANUALSRC=$(KNOWNMANUALSRC) $(filter-out $(KNOWNMANUALSRC),$(ALLMANUALSRC))
MANUALDST=$(subst doc/build/, www/manual/, $(MANUALSRC))

MANUALDIRSRC=$(wildcard doc/??)
MANUALDIRDST=$(subst doc/, www/manual/, $(MANUALDIRSRC)) $(patsubst doc/%, www/manual/%/split/, $(MANUALDIRSRC))

objects=main1.o foo.o main2.o bar.o
mains=main1.o main2.o

all: $(MANUALDIRDST) $(STATICDST) $(MANUALDST) allinonefiles

doc::
	$(MAKE) --no-builtin-rules -$(MAKEFLAGS) -C ./doc

$(MANUALSRC): doc
	$(MAKE) -$(MAKEFLAGS) postprocessmanual # needed to refresh MANUALDST

postprocessmanual:: $(MANUALDIRDST) $(MANUALDST)

$(MANUALDIRDST):
	mkdir -p "$@"

www/manual/en/split/%.html: doc/build/en/split/%.html
	@php ./highlight.php "$<" "$@"

www/manual/ru/split/%.html: doc/build/ru/split/%.html
	@php ./highlight.php "$<" "$@"

www/manual/de/split/%.html: doc/build/de/split/%.html
	@php ./highlight.php "$<" "$@"

allinonefiles: www/manual/en/index.html www/manual/de/index.html www/manual/ru/index.html

www/manual/en/index.html: doc/build/en/book.html
	@php ./highlight.php "$<" "$@"

www/manual/de/index.html: doc/build/de/book.html
	@php ./highlight.php "$<" "$@"

www/manual/ru/index.html: doc/build/ru/book.html
	@php ./highlight.php "$<" "$@"

www/%.html: static/%.html includes/*.php
	@php ./make.php "$<" "$@"

clean_manual:
	rm -rf -- $(MANUALDIRDST)

clean: clean_manual
	rm -- $(STATICDST)
	$(MAKE) -$(MAKEFLAGS) -C ./doc clean
