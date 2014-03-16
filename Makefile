
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

all: website manual allinonefiles

website:: $(STATICDST)

manual:: $(MANUALDIRDST) $(MANUALDST)

doc::
	$(MAKE) --no-builtin-rules -C ./doc

$(MANUALDIRDST):
	mkdir -p "$@"

www/manual/%.html: doc/build/%.html
	@php ./highlight.php "$<" "$@"

allinonefiles: www/manual/en/index.html www/manual/de/index.html www/manual/ru/index.html

www/%.html: static/%.html includes/*.php tpl/page.html
	@php ./make.php "$<" "$@"

clean_manual:
	rm -rf -- $(MANUALDIRDST)

clean: clean_manual
	rm -- $(STATICDST)
	$(MAKE) -C ./doc clean
