// all JavaScript regex patterns are stored here

export const StudentNumRegex = /^([0-9]{9})$/;
export const PasswordRegex = /^((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,64})$/;
export const EmailRegex = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;