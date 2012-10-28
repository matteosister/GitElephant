todo
----

0.8.*

* add interface for caller DONE
* commits count DONE

0.9.*
* isolate objects like grit, clean constructor of Commit, Log, Tag, Tree, Diff by accepting the repository as mandatory argument, and a sha as optional argument
* find a way to populate object props from the sha inside the objects
* inject the caller and the command to the objects to populate props
* use sha (default to HEAD) whenever it's possible inside constructors
* remove the dependency-injection and config dependency

1.0.0
* git blame
* blobs management
* introduce traits and make the library php 5.4 only?
