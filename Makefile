
STATICSRC=$(wildcard static/*.html)
STATICDST=$(subst static/, www/, $(STATICSRC))
	
all: $(STATICDST)
	
www/%.html: static/%.html includes/*.php
	php ./make.php "$<" "$@" 

clean:
	rm $(STATICDST)