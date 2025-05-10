from tkinter import *
from tkinter import messagebox
from db import authenticate

def frame1():
    frame1 = Toplevel()
    frame1.geometry("670x420")
    frame1.title("Baldove_FirstGUI")
    frame1.config(background = '#fff')

    label = Label(frame1,
    text = "WELCOME!",
    font = ('century gothic', 20, 'bold'),
    fg = '#000')
    label.pack()
   
    button =  Button(frame1, text = 'BACK',
                     command = frame1.destroy)
    button.pack()

def login():
    email = email_entry.get()
    password = pass_entry.get()
    if authenticate(email, password):
        messagebox.showinfo("Login Success", "Welcome!")
        frame1()
    else:
        messagebox.showerror("Login Failed", "Invalid email or password.")

window = Tk()
window.geometry("670x420")
window.title("Baldove_FirstGUI")
window.config(background = '#000')

label = Label(window, text = "Hello", font = ('Arial', 20, 'italic'), fg = '#000', bg = 'gray')
label.pack()

Label(window, text = 'email').pack()
email_entry = Entry(window)
email_entry.pack()

Label(window, text = 'pass').pack()
pass_entry = Entry(window, show = '*')
pass_entry.pack()

button =  Button(text = 'submit', command = login)
button.pack()

window.mainloop() 