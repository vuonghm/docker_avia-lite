# Note this is NOT a relocatable thing :)
%define name		intltool
%define ver		0.36.2
%define RELEASE		1
%define rel		%{?CUSTOM_RELEASE} %{!?CUSTOM_RELEASE:%RELEASE}

Name:		%name
Summary:	This module contains scripts and autoconf magic for internationalizing various kinds of data files.
Version: 	%ver
Release: 	%rel
Copyright: 	GPL
Group:		Development/Tools
Source: 	%{name}-%{ver}.tar.gz
URL: 		http://www.gnome.org/
BuildRoot:	/var/tmp/%{name}-%{ver}-root
BuildArch:      noarch
Obsoletes:      xml-i18n-tools
Provides:       xml-i18n-tools = 0.11

%description
** Automatically extracts translatable strings from oaf, glade, bonobo
   ui, nautilus theme and other files into the po files.

** Automatically merges translations from po files back into .oaf files
  (encoding to be 7-bit clean). Also merges into other kinds of files.

%changelog
* Wed Aug 28 2002 Gregory Leblanc <gleblanc@linuxweasel.com>
- marked man pages as docs
- changed owner to root
- removed empty post and postun scripts
- removed manual stripping of binaries.
- made setup quiet

* Wed Sep 19 2001 John Gotts <jgotts@linuxsavvy.com>
- Improved the URL.  Added the appropriate Obsoletes:.  Removed the incorrect
  and preliminary development package stuff.  The datadir changed names and the
  files shouldn't be executable.  Added the man pages to the build.

* Sun Feb 18 2001 Gregory Leblanc <gleblanc@cu-portland.edu>
- Changes to make the spec file more portable across RPM based
  systems.  Changes mainly consisted of using macros better, and
  removing any hard-coded paths.

* Thu Jan 04 2000 Robin * Slomkowski <rslomkow@eazel.com>
- created this thing

%prep
%setup -q

%build
%ifarch alpha
  MYARCH_FLAGS="--host=alpha-redhat-linux"
%endif

LC_ALL=""
LINGUAS=""
LANG=""
export LC_ALL LINGUAS LANG

CFLAGS="$RPM_OPT_FLAGS" ./configure $MYARCH_FLAGS --prefix=%{_prefix} \
	--sysconfdir=%{_sysconfdir}

if [ "$SMP" != "" ]; then
  (make "MAKE=make -k -j $SMP"; exit 0)
  make
else
  make
fi

%install
[ -n "$RPM_BUILD_ROOT" -a "$RPM_BUILD_ROOT" != / ] && rm -rf $RPM_BUILD_ROOT

make prefix=$RPM_BUILD_ROOT%{_prefix} sysconfdir=$RPM_BUILD_ROOT%{_sysconfdir} mandir=$RPM_BUILD_ROOT%{_mandir} install

%clean
[ -n "$RPM_BUILD_ROOT" -a "$RPM_BUILD_ROOT" != / ] && rm -rf $RPM_BUILD_ROOT


%files

%defattr(0555, root, root)
%{_bindir}/*

%defattr (0444, root, root)
%doc AUTHORS COPYING ChangeLog NEWS README
%doc %{_mandir}/man8/*
%{_datadir}/aclocal/*
%{_datadir}/intltool/*
