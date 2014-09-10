.PHONY: serve clean

serve:
	jekyll serve --baseurl ''

clean:
	rm -rf './_site'