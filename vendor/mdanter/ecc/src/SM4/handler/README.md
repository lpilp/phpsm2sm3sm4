# *handle/* 目录

本目录包含了加密过程中的核心算法

* `\SM3\handler\JHandler`
    根据J的不同，进行不同处理的抽象类
    * `\SM3\handler\SmalJHandler`
        变量j处于较小值时的处理类
    * `\SM3\handler\BigJHandler`
        变量j处于较大值时的处理类
* `\SM3\handler\Substitution`
    置换函数类