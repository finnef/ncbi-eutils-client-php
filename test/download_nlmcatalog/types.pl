#!/usr/bin/perl

use strict;
use warnings;

#2011-10-05-11-15-58.xml:        <TypeOfResource>Serial</TypeOfResource>
#2011-10-05-11-15-58.xml:        <TypeOfResource>Electronic Resource</TypeOfResource>
#2011-10-05-11-15-58.xml:        <TypeOfResource>Book</TypeOfResource>
#2011-10-05-11-15-58.xml:        <TypeOfResource>Book</TypeOfResource>
#2011-10-05-11-15-58.xml:        <TypeOfResource>Book</TypeOfResource>
#2011-10-05-11-15-58.xml:        <TypeOfResource>Serial</TypeOfResource>
#2011-10-05-11-15-58.xml:        <TypeOfResource>Book Chapter</TypeOfResource>
#2011-10-05-11-15-58.xml:        <TypeOfResource>Book</TypeOfResource>
#2011-10-05-11-15-58.xml:        <TypeOfResource>Collection (print)</TypeOfResource>
#2011-10-05-11-15-58.xml:        <TypeOfResource>Collection (print)</TypeOfResource>

my %types;

while (<>) {
    /TypeOfResource>(.+)<\/TypeOfResource/;
    my $type = $1;
    #print "$type\n";
    if (!(exists $types{$type})) {
        $types{$type} = 0;
    }
    $types{$type}++;
}

while (my($type, $count) = each %types) {
    print "$type: $count\n";
}
