select '$epsg[' || srid || '] = ' ||  split_part(split_part(srtext, '[', 2), ',', 1) || ';'

from spatial_ref_sys

order by srid