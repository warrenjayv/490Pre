
def stringSub(a, b): 
   diff = a - b
   return switch(diff)
   
def switch(diff):
   switcher = {
       0: "zero",
       1: "one",
       2: "two",
       3: "three",
       4: "four",
       5: "five",
       6: "six",
       7: "seven",
       8: "eight",
       9: "nine",
   }
   return switcher.get(diff, "nothing")
print(stringSub(-3, -9))
print(stringSub(9, 9))
print(stringSub(50,9))
print(stringSub(5,2))